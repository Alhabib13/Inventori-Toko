<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\StockMovementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $search = trim($request->string('search')->toString());
        $cashierSummary = null;

        $transactions = Transaction::query()
            ->with('kasir')
            ->when($user?->role === 'kasir', function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id);
            })
            ->when($user?->role === 'owner', function (Builder $query) use ($user): void {
                $query->whereHas('kasir', fn (Builder $kasirQuery) => $this->scopeToUserStore($kasirQuery, $user));
            })
            ->when($dateFrom !== '', function (Builder $query) use ($dateFrom): void {
                $query->whereDate('tanggal_transaksi', '>=', $dateFrom);
            })
            ->when($dateTo !== '', function (Builder $query) use ($dateTo): void {
                $query->whereDate('tanggal_transaksi', '<=', $dateTo);
            })
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('kode_transaksi', 'like', "%{$search}%")
                        ->orWhereHas('detailItem', function (Builder $itemQuery) use ($search): void {
                            $itemQuery->where('nama_produk', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('tanggal_transaksi')
            ->paginate(10)
            ->withQueryString();

        if ($user?->role === 'kasir') {
            $todayTransactionsQuery = Transaction::query()
                ->where('user_id', $user->id)
                ->whereDate('tanggal_transaksi', now()->toDateString());

            $activeTodayTransactionsQuery = (clone $todayTransactionsQuery)
                ->where('status', '!=', 'dibatalkan');

            $cashierSummary = [
                'transaction_count' => $activeTodayTransactionsQuery->count(),
                'sales_total' => (float) $activeTodayTransactionsQuery->sum('total_bayar'),
                'items_sold_total' => (int) $activeTodayTransactionsQuery->sum('total_item'),
                'cancelled_count' => (clone $todayTransactionsQuery)->where('status', 'dibatalkan')->count(),
            ];
        }

        return view('transactions.index', [
            'transactions' => $transactions,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
            'isKasir' => $user?->role === 'kasir',
            'cashierSummary' => $cashierSummary,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $activeProductsQuery = Product::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
            ->where('is_active', true)
            ->where('stok', '>', 0);

        $products = (clone $activeProductsQuery)
            ->with('kategori:id,nama_kategori')
            ->select(['id', 'category_id', 'store_id', 'store_name', 'nama_produk', 'kode_produk', 'harga_jual', 'stok', 'stok_minimum', 'satuan'])
            ->orderBy('nama_produk')
            ->paginate(10)
            ->withQueryString();

        $storeProfile = User::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
            ->where('role', 'owner')
            ->first(['store_id', 'store_name', 'alamat_toko']);

        $posSummary = [
            'active_products_count' => (clone $activeProductsQuery)->count(),
            'low_stock_count' => Product::query()
                ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
                ->where('is_active', true)
                ->whereColumn('stok', '<=', 'stok_minimum')
                ->count(),
            'today_transaction_count' => $user?->role === 'kasir'
                ? Transaction::query()
                    ->where('user_id', $user->id)
                    ->whereDate('tanggal_transaksi', now()->toDateString())
                    ->where('status', '!=', 'dibatalkan')
                    ->count()
                : 0,
        ];

        return view('transactions.create', [
            'products' => $products,
            'posSummary' => $posSummary,
            'storeProfile' => $storeProfile,
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
                ->tap(fn ($query) => $this->scopeToUserStore($query, $request->user()))
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->pluck('product_id')->unique()->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk tidak valid untuk toko ini.',
                ]);
            }

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

            if ($paidAmount < $totalPayment) {
                throw ValidationException::withMessages([
                    'nominal_bayar' => 'Nominal bayar tidak boleh kurang dari total bayar.',
                ]);
            }

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
                    'harga_beli' => $product->harga_beli,
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

    public function show(Request $request, Transaction $transaction): View
    {
        $user = $request->user();

        $this->abortIfTransactionOutsideStore($transaction, $user);

        $transaction->load(['kasir', 'detailItem.produk']);

        return view('transactions.show', [
            'transaction' => $transaction,
            'canCancelTransaction' => $user?->role === 'owner' && $transaction->status !== 'dibatalkan',
        ]);
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->abortIfTransactionOutsideStore($transaction, $request->user());

        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->abortIfTransactionOutsideStore($transaction, $request->user());

        return redirect()->route('transactions.index');
    }

    public function destroy(Request $request, Transaction $transaction, StockMovementService $stockMovementService): RedirectResponse
    {
        $user = $request->user();

        if ($user?->role !== 'owner') {
            abort(403, 'Anda tidak memiliki akses untuk membatalkan transaksi ini.');
        }

        $this->abortIfTransactionOutsideStore($transaction, $user);

        if ($transaction->status === 'dibatalkan') {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('status', 'Transaksi sudah dibatalkan sebelumnya.');
        }

        $transaction->load('detailItem.produk');

        DB::transaction(function () use ($transaction, $user, $stockMovementService): void {
            foreach ($transaction->detailItem as $item) {
                if (! $item->produk) {
                    continue;
                }

                $stockMovementService->recordIncoming(
                    product: $item->produk,
                    qty: $item->qty,
                    user: $user,
                    note: 'Pembatalan transaksi penjualan '.$transaction->kode_transaksi,
                    referenceType: 'transaction_cancellation',
                    referenceId: $transaction->id,
                );
            }

            $transaction->update([
                'status' => 'dibatalkan',
            ]);
        });

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('status', 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.');
    }

    private function makeTransactionCode(): string
    {
        do {
            $code = 'TRX-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Transaction::query()->where('kode_transaksi', $code)->exists());

        return $code;
    }

    private function abortIfTransactionOutsideStore(Transaction $transaction, $user): void
    {
        if ($user?->role === 'kasir' && $transaction->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        if ($user?->role === 'owner' && ! $this->modelBelongsToUserStore($transaction->kasir, $user)) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }
    }
}
