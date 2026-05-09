<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Services\StockMovementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::query()
            ->with('kasir')
            ->latest('tanggal_transaksi')
            ->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function create(): View
    {
        return view('transactions.create', [
            'products' => Product::query()
                ->where('is_active', true)
                ->where('stok', '>', 0)
                ->orderBy('nama_produk')
                ->get(),
        ]);
    }

    public function store(Request $request, StockMovementService $stockMovementService): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.qty' => ['nullable', 'integer', 'min:0'],
            'diskon' => ['nullable', 'numeric', 'min:0'],
            'pajak' => ['nullable', 'numeric', 'min:0'],
            'nominal_bayar' => ['nullable', 'numeric', 'min:0'],
            'metode_pembayaran' => ['nullable', 'string', 'max:50'],
            'catatan' => ['nullable', 'string'],
        ]);

        $items = collect($data['items'])
            ->filter(fn (array $item): bool => (int) ($item['qty'] ?? 0) > 0)
            ->values();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Minimal satu produk harus memiliki qty lebih dari 0.',
            ]);
        }

        $transaction = DB::transaction(function () use ($items, $data, $request, $stockMovementService): Transaction {
            $products = Product::query()
                ->whereIn('id', $items->pluck('product_id'))
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $totalItem = 0;

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['qty'];

                $subtotal += (float) $product->harga_jual * $qty;
                $totalItem += $qty;
            }

            $discount = (float) ($data['diskon'] ?? 0);
            $tax = (float) ($data['pajak'] ?? 0);
            $totalPayment = max(0, $subtotal - $discount + $tax);
            $paidAmount = (float) ($data['nominal_bayar'] ?? $totalPayment);

            $transaction = Transaction::create([
                'kode_transaksi' => $this->makeTransactionCode(),
                'user_id' => $request->user()->id,
                'tanggal_transaksi' => now(),
                'total_item' => $totalItem,
                'subtotal' => $subtotal,
                'diskon' => $discount,
                'pajak' => $tax,
                'total_bayar' => $totalPayment,
                'nominal_bayar' => $paidAmount,
                'kembalian' => max(0, $paidAmount - $totalPayment),
                'metode_pembayaran' => $data['metode_pembayaran'] ?? 'tunai',
                'status' => 'selesai',
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);
                $qty = (int) $item['qty'];
                $lineSubtotal = (float) $product->harga_jual * $qty;

                $transaction->detailItem()->create([
                    'product_id' => $product->id,
                    'nama_produk' => $product->nama_produk,
                    'qty' => $qty,
                    'harga' => $product->harga_jual,
                    'subtotal' => $lineSubtotal,
                ]);

                $stockMovementService->recordOutgoing(
                    product: $product,
                    qty: $qty,
                    user: $request->user(),
                    note: 'Transaksi penjualan '.$transaction->kode_transaksi,
                    referenceType: 'transaction',
                    referenceId: $transaction->id,
                );
            }

            return $transaction;
        });

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('status', 'Transaksi berhasil disimpan.');
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['kasir', 'detailItem']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        return redirect()->route('transactions.index');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        return redirect()->route('transactions.index');
    }

    private function makeTransactionCode(): string
    {
        do {
            $code = 'TRX-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Transaction::query()->where('kode_transaksi', $code)->exists());

        return $code;
    }
}
