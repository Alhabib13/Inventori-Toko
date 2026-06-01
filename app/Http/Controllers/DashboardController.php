<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesForecast;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $isSimpleMode = $user?->mode_app === 'sederhana';
        $trendPeriod = request()->integer('trend', 7);

        if (! in_array($trendPeriod, [7, 30], true)) {
            $trendPeriod = 7;
        }

        $salesTotal = (float) Transaction::query()
            ->whereHas('kasir', fn ($query) => $this->scopeToUserStore($query, $user))
            ->sum('total_bayar');
        $grossProfitTotal = $this->grossProfitQuery($user)->sum(DB::raw('transaction_items.qty * (transaction_items.harga - products.harga_beli)'));
        $purchaseTotal = (float) Purchase::query()
            ->whereHas('pengguna', fn ($query) => $this->scopeToUserStore($query, $user))
            ->sum('total_bayar');
        $productScope = $this->scopeToUserStore(Product::query(), $user);
        $stockTotal = (int) (clone $productScope)->sum('stok');
        $productCount = (clone $productScope)->count();
        $stockValue = (float) (clone $productScope)->sum(DB::raw('stok * harga_beli'));
        $activeSuppliers = Supplier::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
            ->where('is_active', true)
            ->count();
        $criticalProductsCount = (clone $productScope)->whereColumn('stok', '<=', 'stok_minimum')->count();
        $criticalProducts = Product::query()
            ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderByRaw('(stok_minimum - stok) DESC')
            ->take(5)
            ->get(['id', 'nama_produk', 'kode_produk', 'stok', 'stok_minimum', 'satuan']);

        $forecastHighlights = SalesForecast::query()
            ->with('produk')
            ->whereHas('produk', function ($query) use ($user): void {
                $this->scopeToUserStore($query, $user);
            })
            ->latest()
            ->take(5)
            ->get();

        $forecastCount = $forecastHighlights->count();
        $forecastRestockTotal = (int) $forecastHighlights->sum('selisih_prediksi');
        $forecastRestockCount = $forecastHighlights->where('selisih_prediksi', '>', 0)->count();
        $criticalStockGapTotal = (int) $criticalProducts->sum(fn ($product) => max(0, $product->stok_minimum - $product->stok));
        $latestPurchase = Purchase::query()
            ->whereHas('pengguna', fn ($query) => $this->scopeToUserStore($query, $user))
            ->latest('tanggal_pembelian')
            ->first(['kode_pembelian', 'tanggal_pembelian', 'total_bayar']);
        $canManageInventory = $user?->mode_app === 'sederhana';
        $salesTrend = collect(range($trendPeriod - 1, 0))
            ->map(function (int $daysAgo) use ($user) {
                $date = Carbon::now()->subDays($daysAgo);

                return [
                    'label' => $date->translatedFormat('d M'),
                    'date' => $date->toDateString(),
                    'total' => (float) Transaction::query()
                        ->whereHas('kasir', fn ($query) => $this->scopeToUserStore($query, $user))
                        ->whereDate('tanggal_transaksi', $date->toDateString())
                        ->sum('total_bayar'),
                    'profit' => (float) $this->grossProfitQuery($user)
                        ->whereDate('transactions.tanggal_transaksi', $date->toDateString())
                        ->sum(DB::raw('transaction_items.qty * (transaction_items.harga - products.harga_beli)')),
                ];
            });
        $salesTrendTotal = (float) $salesTrend->sum('total');
        $salesTrendAverage = (float) $salesTrend->avg('total');
        $salesTrendProfitTotal = (float) $salesTrend->sum('profit');
        $salesTrendProfitAverage = (float) $salesTrend->avg('profit');
        $todaySalesScope = Transaction::query()
            ->whereHas('kasir', fn ($query) => $this->scopeToUserStore($query, $user))
            ->whereDate('tanggal_transaksi', now()->toDateString())
            ->where('status', '!=', 'dibatalkan');
        $todaySalesCount = (clone $todaySalesScope)->count();
        $todaySalesTotal = (float) (clone $todaySalesScope)->sum('total_bayar');
        $todayGrossProfit = (float) $this->grossProfitQuery($user)
            ->whereDate('transactions.tanggal_transaksi', now()->toDateString())
            ->sum(DB::raw('transaction_items.qty * (transaction_items.harga - products.harga_beli)'));

        return view('dashboard.index', [
            'isSimpleMode' => $isSimpleMode,
            'salesTotal' => $salesTotal,
            'grossProfitTotal' => $grossProfitTotal,
            'purchaseTotal' => $purchaseTotal,
            'stockTotal' => $stockTotal,
            'productCount' => $productCount,
            'stockValue' => $stockValue,
            'activeSuppliers' => $activeSuppliers,
            'criticalProductsCount' => $criticalProductsCount,
            'criticalProducts' => $criticalProducts,
            'forecastHighlights' => $forecastHighlights,
            'forecastCount' => $forecastCount,
            'forecastRestockTotal' => $forecastRestockTotal,
            'forecastRestockCount' => $forecastRestockCount,
            'criticalStockGapTotal' => $criticalStockGapTotal,
            'latestPurchase' => $latestPurchase,
            'canManageInventory' => $canManageInventory,
            'salesTrend' => $salesTrend,
            'trendPeriod' => $trendPeriod,
            'salesTrendTotal' => $salesTrendTotal,
            'salesTrendAverage' => $salesTrendAverage,
            'salesTrendProfitTotal' => $salesTrendProfitTotal,
            'salesTrendProfitAverage' => $salesTrendProfitAverage,
            'todaySalesCount' => $todaySalesCount,
            'todaySalesTotal' => $todaySalesTotal,
            'todayGrossProfit' => $todayGrossProfit,
        ]);
    }

    private function grossProfitQuery($user)
    {
        $query = TransactionItem::query()
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('transactions.status', '!=', 'dibatalkan');

        return $this->scopeToUserStore($query, $user, 'users.store_id', 'users.store_name');
    }
}
