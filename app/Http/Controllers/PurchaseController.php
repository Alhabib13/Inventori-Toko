<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\StockMovementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $search = trim((string) $request->string('search'));

        $purchases = Purchase::query()
            ->with(['supplier', 'pengguna'])
            ->when($user?->role === 'owner', function (Builder $query) use ($user): void {
                $query->whereHas('pengguna', fn (Builder $penggunaQuery) => $this->scopeToUserStore($penggunaQuery, $user));
            })
            ->when($user?->role === 'gudang', function (Builder $query) use ($user): void {
                $query->whereHas('pengguna', fn (Builder $penggunaQuery) => $this->scopeToUserStore($penggunaQuery, $user));
            })
            ->when($dateFrom !== '', function (Builder $query) use ($dateFrom): void {
                $query->whereDate('tanggal_pembelian', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function (Builder $query) use ($dateTo): void {
                $query->whereDate('tanggal_pembelian', '<=', $dateTo);
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $purchaseQuery) use ($search): void {
                    $purchaseQuery
                        ->where('kode_pembelian', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn (Builder $supplierQuery) => $supplierQuery->where('nama_supplier', 'like', "%{$search}%"))
                        ->orWhereHas('pengguna', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('tanggal_pembelian')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'canManagePurchases' => $user?->role === 'gudang' && $user?->mode_app === 'lengkap',
            'isOwner' => $user?->role === 'owner',
        ]);
    }

    public function create(): View
    {
        $user = request()->user();

        return view('purchases.create', [
            'suppliers' => Supplier::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
                ->where('is_active', true)
                ->orderBy('nama_supplier')
                ->get(),
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
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
            'items.*.harga_beli' => ['nullable', 'numeric', 'min:0'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'ongkir' => ['nullable', 'numeric', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        $items = collect($data['items'])
            ->filter(function (array $item): bool {
                return (int) ($item['qty'] ?? 0) > 0 && (float) ($item['harga_beli'] ?? 0) >= 0;
            })
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu item pembelian harus memiliki qty lebih dari 0.',
            ]);
        }

        if ($items->pluck('product_id')->count() !== $items->pluck('product_id')->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Produk yang sama tidak boleh dimasukkan lebih dari satu baris pembelian.',
            ]);
        }

        $supplier = Supplier::query()
            ->whereKey($data['supplier_id'])
            ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
            ->first();

        if (! $supplier) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Supplier tidak valid untuk toko ini.',
            ]);
        }

        $purchase = DB::transaction(function () use ($items, $data, $request, $stockMovementService): Purchase {
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->pluck('product_id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk tidak valid untuk toko ini.',
                ]);
            }

            $subtotal = 0;

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                $buyPrice = (float) $item['harga_beli'];
                $subtotal += $qty * $buyPrice;
            }

            $discount = (float) ($data['diskon'] ?? 0);
            $shipping = (float) ($data['ongkir'] ?? 0);
            $totalPayment = max(0, $subtotal - $discount + $shipping);

            $purchase = Purchase::create([
                'kode_pembelian' => $this->makePurchaseCode(),
                'supplier_id' => $data['supplier_id'],
                'user_id' => $request->user()->id,
                'tanggal_pembelian' => now(),
                'subtotal' => $subtotal,
                'diskon' => $discount,
                'ongkir' => $shipping,
                'total_bayar' => $totalPayment,
                'status' => 'selesai',
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['qty'];
                $buyPrice = (float) $item['harga_beli'];
                $lineSubtotal = $qty * $buyPrice;

                $purchase->detailItem()->create([
                    'product_id' => $product->id,
                    'nama_produk' => $product->nama_produk,
                    'qty' => $qty,
                    'harga_beli' => $buyPrice,
                    'harga_beli_sebelum' => $product->harga_beli,
                    'subtotal' => $lineSubtotal,
                ]);

                $product->update(['harga_beli' => $buyPrice]);

                $stockMovementService->recordIncoming(
                    product: $product,
                    qty: $qty,
                    user: $request->user(),
                    note: 'Pembelian '.$purchase->kode_pembelian,
                    referenceType: 'purchase',
                    referenceId: $purchase->id,
                );
            }

            return $purchase;
        });

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('status', 'Pembelian berhasil disimpan.');
    }

    public function show(Request $request, Purchase $purchase): View
    {
        $user = $request->user();

        $this->abortIfPurchaseOutsideStore($purchase, $user);

        $purchase->load(['supplier', 'pengguna', 'detailItem.produk']);

        return view('purchases.show', [
            'purchase' => $purchase,
            'canCancelPurchase' => $user?->role === 'gudang' && $user?->mode_app === 'lengkap' && $purchase->status !== 'dibatalkan',
        ]);
    }

    public function destroy(Request $request, Purchase $purchase, StockMovementService $stockMovementService): RedirectResponse
    {
        $user = $request->user();

        if ($user?->role !== 'gudang' || $user?->mode_app !== 'lengkap') {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan pembelian ini.');
        }

        $this->abortIfPurchaseOutsideStore($purchase, $user);

        if ($purchase->status === 'dibatalkan') {
            return redirect()
                ->route('purchases.show', $purchase)
                ->with('status', 'Pembelian sudah dibatalkan sebelumnya.');
        }

        $purchase->load('detailItem.produk');

        try {
            DB::transaction(function () use ($purchase, $user, $stockMovementService): void {
                foreach ($purchase->detailItem as $item) {
                    if (! $item->produk) {
                        throw ValidationException::withMessages([
                            'items' => 'Salah satu produk pada pembelian ini sudah tidak tersedia untuk rollback stok.',
                        ]);
                    }

                    $stockMovementService->recordOutgoing(
                        product: $item->produk,
                        qty: $item->qty,
                        user: $user,
                        note: 'Pembatalan pembelian '.$purchase->kode_pembelian,
                        referenceType: 'purchase_cancellation',
                        referenceId: $purchase->id,
                    );

                    if (
                        filled($item->harga_beli_sebelum)
                        && (float) $item->produk->harga_beli === (float) $item->harga_beli
                    ) {
                        $item->produk->update([
                            'harga_beli' => $item->harga_beli_sebelum,
                        ]);
                    }
                }

                $purchase->update([
                    'status' => 'dibatalkan',
                ]);
            });
        } catch (ValidationException $exception) {
            return redirect()
                ->route('purchases.show', $purchase)
                ->withErrors($exception->errors())
                ->withInput();
        }

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('status', 'Pembelian berhasil dibatalkan dan stok telah disesuaikan.');
    }

    private function makePurchaseCode(): string
    {
        do {
            $code = 'PO-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Purchase::query()->where('kode_pembelian', $code)->exists());

        return $code;
    }

    private function abortIfPurchaseOutsideStore(Purchase $purchase, $user): void
    {
        if (! in_array($user?->role, ['owner', 'gudang'], true)) {
            abort(403, 'Anda tidak memiliki akses ke pembelian ini.');
        }

        if (! $this->modelBelongsToUserStore($purchase->pengguna, $user)) {
            abort(403, 'Anda tidak memiliki akses ke pembelian ini.');
        }
    }
}
