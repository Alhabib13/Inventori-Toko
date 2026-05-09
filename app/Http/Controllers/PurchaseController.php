<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        $purchases = Purchase::query()
            ->with(['supplier', 'pengguna'])
            ->latest('tanggal_pembelian')
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::query()
                ->where('is_active', true)
                ->orderBy('nama_supplier')
                ->get(),
            'products' => Product::query()
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

        $purchase = DB::transaction(function () use ($items, $data, $request, $stockMovementService): Purchase {
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->get()
                ->keyBy('id');

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

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'pengguna', 'detailItem']);

        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        return view('purchases.edit', compact('purchase'));
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        return redirect()->route('purchases.index');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        return redirect()->route('purchases.index');
    }

    private function makePurchaseCode(): string
    {
        do {
            $code = 'PO-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Purchase::query()->where('kode_pembelian', $code)->exists());

        return $code;
    }
}
