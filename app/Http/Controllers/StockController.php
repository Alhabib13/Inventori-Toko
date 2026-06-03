<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(): View
    {
        return $this->stockListingView();
    }

    public function notifications(): View
    {
        return $this->stockListingView(true);
    }

    public function create(): View
    {
        $user = request()->user();

        return view('stocks.create', [
            'products' => Product::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
                ->where('is_active', true)
                ->orderBy('nama_produk')
                ->get(),
        ]);
    }

    public function store(Request $request, StockMovementService $stockMovementService): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'catatan' => ['nullable', 'string'],
        ]);

        $product = Product::query()
            ->whereKey($data['product_id'])
            ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
            ->firstOrFail();

        $stockMovementService->recordIncoming(
            product: $product,
            qty: (int) $data['qty'],
            user: $request->user(),
            note: $data['catatan'] ?? null,
            referenceType: 'manual',
        );

        return redirect()
            ->route('stocks.index')
            ->with('status', 'Stok masuk berhasil dicatat.');
    }

    public function show(StockMovement $stock): View
    {
        if (! $this->modelBelongsToUserStore($stock->produk, request()->user())) {
            abort(403, 'Anda tidak memiliki akses ke pergerakan stok ini.');
        }

        $stock->load(['produk', 'pengguna']);

        return view('stocks.show', ['stock' => $stock]);
    }

    public function updateProductStock(Request $request, Product $product, StockMovementService $stockMovementService): RedirectResponse
    {
        abort_unless($this->canManageStock($request->user()?->role, $request->user()?->mode_app), 403);

        if (! $this->modelBelongsToUserStore($product, $request->user())) {
            abort(403, 'Anda tidak memiliki akses ke produk ini.');
        }

        $data = $request->validate([
            'stok' => ['required', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $targetStock = (int) $data['stok'];
        $currentStock = (int) $product->stok;

        if ($targetStock === $currentStock) {
            return back()->with('status', 'Stok tidak berubah.');
        }

        $qty = abs($targetStock - $currentStock);
        $note = filled($data['catatan'] ?? null)
            ? $data['catatan']
            : 'Edit stok langsung dari halaman stok.';

        if ($targetStock > $currentStock) {
            $stockMovementService->recordIncoming(
                product: $product,
                qty: $qty,
                user: $request->user(),
                note: $note,
                referenceType: 'manual',
            );
        } else {
            $stockMovementService->recordOutgoing(
                product: $product,
                qty: $qty,
                user: $request->user(),
                note: $note,
                referenceType: 'manual',
            );
        }

        return back()->with('status', 'Stok produk berhasil diperbarui.');
    }

    private function stockListingView(bool $showLowStockOnly = false): View
    {
        $search = trim((string) request()->query('search', ''));
        $movementSearch = trim((string) request()->query('movement_search', ''));

        $baseProductsQuery = Product::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, request()->user()))
            ->when($showLowStockOnly, fn ($query) => $query->whereColumn('stok', '<=', 'stok_minimum'))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($productQuery) use ($search): void {
                    $productQuery
                        ->where('nama_produk', 'like', "%{$search}%")
                        ->orWhere('kode_produk', 'like', "%{$search}%")
                        ->orWhereHas('kategori', fn ($categoryQuery) => $categoryQuery->where('nama_kategori', 'like', "%{$search}%"))
                        ->orWhereHas('supplier', fn ($supplierQuery) => $supplierQuery->where('nama_supplier', 'like', "%{$search}%"));
                });
            });

        $products = (clone $baseProductsQuery)
            ->with(['kategori', 'supplier'])
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        $totalProducts = (clone $baseProductsQuery)->count();
        $lowStockCount = (clone $baseProductsQuery)->whereColumn('stok', '<=', 'stok_minimum')->count();
        $safeStockCount = max($totalProducts - $lowStockCount, 0);

        $movements = StockMovement::query()
            ->with(['produk', 'pengguna'])
            ->whereHas('produk', function ($query): void {
                $this->scopeToUserStore($query, request()->user());
            })
            ->when($movementSearch !== '', function ($query) use ($movementSearch): void {
                $query->where(function ($movementQuery) use ($movementSearch): void {
                    $movementQuery
                        ->where('jenis_pergerakan', 'like', "%{$movementSearch}%")
                        ->orWhere('catatan', 'like', "%{$movementSearch}%")
                        ->orWhereHas('produk', function ($productQuery) use ($movementSearch): void {
                            $productQuery
                                ->where('nama_produk', 'like', "%{$movementSearch}%")
                                ->orWhere('kode_produk', 'like', "%{$movementSearch}%");
                        });
                });
            })
            ->latest('tanggal_pergerakan')
            ->paginate(10, ['*'], 'movements_page')
            ->withQueryString();

        return view('stocks.index', [
            'products' => $products,
            'movements' => $movements,
            'canManageStock' => $this->canManageStock(request()->user()?->role, request()->user()?->mode_app),
            'showLowStockOnly' => $showLowStockOnly,
            'search' => $search,
            'movementSearch' => $movementSearch,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'safeStockCount' => $safeStockCount,
        ]);
    }

    private function canManageStock(?string $role, ?string $modeApp): bool
    {
        return match ($role) {
            'owner' => $modeApp === 'sederhana',
            'gudang' => $modeApp === 'lengkap',
            default => false,
        };
    }
}
