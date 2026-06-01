<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\SalesForecast;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\ProductImportService;
use App\Services\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductImportService $productImportService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->string('search'));

        $products = Product::query()
            ->with(['kategori', 'supplier']);

        $products = $this->scopeToUserStore($products, $user)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($productQuery) use ($search) {
                    $productQuery
                        ->where('nama_produk', 'like', "%{$search}%")
                        ->orWhere('kode_produk', 'like', "%{$search}%")
                        ->orWhere('satuan', 'like', "%{$search}%")
                        ->orWhereHas('kategori', fn ($categoryQuery) => $categoryQuery->where('nama_kategori', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('nama_supplier', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'canManageProducts' => $this->canManageProducts($user?->role, $user?->mode_app),
            'requiresSupplier' => $user?->mode_app !== 'sederhana',
            'search' => $search,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
        ], [
            'import_file.mimes' => 'File import harus berformat CSV. Untuk file Excel, simpan dulu sebagai CSV.',
            'import_file.max' => 'Ukuran file import maksimal 50 MB.',
        ]);

        $result = $this->productImportService->importFromCsv(
            $validated['import_file'],
            $request->user(),
        );

        $statusMessage = "Import produk selesai. {$result['imported']} produk baru ditambahkan.";

        if ($result['skipped'] > 0) {
            $statusMessage .= " {$result['skipped']} baris duplikat dilewati.";
        }

        return redirect()
            ->route('products.index')
            ->with('status', $statusMessage);
    }

    public function downloadTemplate(Request $request): StreamedResponse
    {
        abort_unless($this->canManageProducts($request->user()?->role, $request->user()?->mode_app), 403);

        $requiresSupplier = $request->user()?->mode_app !== 'sederhana';
        $headers = $requiresSupplier
            ? ['nama_produk', 'kategori', 'supplier', 'satuan', 'harga_beli', 'harga_jual', 'stok_awal', 'stok_minimum']
            : ['nama_produk', 'kategori', 'satuan', 'harga_beli', 'harga_jual', 'stok_awal', 'stok_minimum'];

        $sampleRows = $requiresSupplier
            ? [
                ['Lampu LED 12W', 'Lampu & Bohlam', 'PT Cahaya Lampu Nusantara', 'PCS', '18000', '25000', '24', '6'],
                ['Kabel NYM 2x1.5', 'Kabel Listrik', 'PT Kabel Elektrik Sentosa', 'METER', '8500', '12000', '100', '20'],
            ]
            : [
                ['Beras Premium 5kg', 'Sembako', 'PCS', '62000', '70000', '20', '5'],
                ['Minyak Goreng 1L', 'Sembako', 'PCS', '14500', '16500', '24', '6'],
            ];

        $filename = $requiresSupplier
            ? 'template_produk_mode_lengkap.csv'
            : 'template_produk_mode_sederhana.csv';

        return response()->streamDownload(function () use ($headers, $sampleRows): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $headers);

            foreach ($sampleRows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function create(): View
    {
        return view('products.create', $this->formOptions(request()->user()?->mode_app));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduct($request);

        Product::create($data + [
            'kode_produk' => $this->makeProductCode(),
            'slug' => $this->makeUniqueSlug($data['nama_produk']),
            'store_id' => $request->user()?->store_id,
            'store_name' => $request->user()?->store_name,
            'stok' => 0,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product): View
    {
        $this->abortIfProductOutsideStore($product, request()->user());
        $product->load(['kategori', 'supplier']);
        $user = request()->user();

        return view('products.show', [
            'product' => $product,
            'canManageProducts' => $this->canManageProducts($user?->role, $user?->mode_app),
            'requiresSupplier' => $user?->mode_app !== 'sederhana',
        ]);
    }

    public function edit(Product $product): View
    {
        $this->abortIfProductOutsideStore($product, request()->user());
        return view('products.edit', $this->formOptions(request()->user()?->mode_app) + compact('product'));
    }

    public function update(Request $request, Product $product, StockMovementService $stockMovementService): RedirectResponse
    {
        $this->abortIfProductOutsideStore($product, $request->user());
        $data = $this->validateProduct($request);
        $targetStock = (int) $request->integer('stok', $product->stok);
        unset($data['stok']);

        $product->update($data + [
            'slug' => $this->makeUniqueSlug($data['nama_produk'], $product),
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($this->canManageProducts($request->user()?->role, $request->user()?->mode_app) && $targetStock !== $product->fresh()->stok) {
            $this->syncProductStock(
                $product->fresh(),
                $targetStock,
                $request->user(),
                $stockMovementService,
            );
        }

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->abortIfProductOutsideStore($product, request()->user());
        $hasTransactionItems = DB::table('transaction_items')->where('product_id', $product->id)->exists();
        $hasPurchaseItems = PurchaseItem::query()->where('product_id', $product->id)->exists();

        if ($hasTransactionItems || $hasPurchaseItems) {
            return redirect()
                ->route('products.index')
                ->with('status', 'Produk tidak bisa dihapus karena sudah dipakai transaksi atau pembelian.');
        }

        StockMovement::query()->where('product_id', $product->id)->delete();
        SalesForecast::query()->where('product_id', $product->id)->delete();
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    public function destroyAll(): RedirectResponse
    {
        abort_unless($this->canManageProducts(request()->user()?->role, request()->user()?->mode_app), 403);

        $productIds = Product::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, request()->user()))
            ->pluck('id');

        $hasTransactionItems = DB::table('transaction_items')->whereIn('product_id', $productIds)->exists();
        $hasPurchaseItems = PurchaseItem::query()->whereIn('product_id', $productIds)->exists();

        if ($hasTransactionItems || $hasPurchaseItems) {
            return redirect()
                ->route('products.index')
                ->with('status', 'Hapus semua produk dibatalkan karena sudah ada transaksi atau pembelian yang memakai produk.');
        }

        if ($productIds->isEmpty()) {
            return redirect()
                ->route('products.index')
                ->with('status', 'Tidak ada produk yang perlu dihapus.');
        }

        StockMovement::query()->whereIn('product_id', $productIds)->delete();
        SalesForecast::query()->whereIn('product_id', $productIds)->delete();
        Product::query()->whereIn('id', $productIds)->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Semua produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateProduct(Request $request): array
    {
        $requiresSupplier = $request->user()?->mode_app !== 'sederhana';
        $user = $request->user();

        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($query) => $this->scopeToUserStore($query, $user))],
            'supplier_id' => $requiresSupplier
                ? ['required', Rule::exists('suppliers', 'id')->where(fn ($query) => $this->scopeToUserStore($query, $user))]
                : ['nullable'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'harga_jual' => ['required', 'numeric', 'min:0', 'gte:harga_beli'],
            'stok' => ['nullable', 'integer', 'min:0'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'satuan' => ['required', 'string', 'max:50'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        if (! $requiresSupplier) {
            $validated['supplier_id'] = null;
        }

        return $validated;
    }

    private function syncProductStock(
        Product $product,
        int $targetStock,
        $user,
        StockMovementService $stockMovementService,
    ): void {
        $currentStock = $product->stok;

        if ($targetStock === $currentStock) {
            return;
        }

        $difference = abs($targetStock - $currentStock);
        $note = 'Penyesuaian stok dari edit produk.';

        if ($targetStock > $currentStock) {
            $stockMovementService->recordIncoming($product, $difference, $user, $note, 'manual');

            return;
        }

        $stockMovementService->recordOutgoing($product, $difference, $user, $note, 'manual');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(?string $modeApp): array
    {
        $user = request()->user();

        return [
            'requiresSupplier' => $modeApp !== 'sederhana',
            'categories' => Category::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
                ->where('is_active', true)
                ->orderBy('nama_kategori')
                ->get(),
            'suppliers' => Supplier::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
                ->where('is_active', true)
                ->orderBy('nama_supplier')
                ->get(),
        ];
    }

    private function canManageProducts(?string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => $modeApp === 'sederhana',
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }

    private function makeProductCode(): string
    {
        do {
            $code = 'PRD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Product::query()->where('kode_produk', $code)->exists());

        return $code;
    }

    private function makeUniqueSlug(string $name, ?Product $product = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($product, fn ($query) => $query->whereKeyNot($product->getKey()))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function abortIfProductOutsideStore(Product $product, $user): void
    {
        if (! $this->modelBelongsToUserStore($product, $user)) {
            abort(403, 'Anda tidak memiliki akses ke produk ini.');
        }
    }
}
