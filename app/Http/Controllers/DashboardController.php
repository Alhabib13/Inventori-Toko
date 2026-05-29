<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesForecast;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $isSimpleMode = $user?->mode_app === 'sederhana';
        $storeName = $user?->store_name;
        $trendPeriod = request()->integer('trend', 7);

        if (! in_array($trendPeriod, [7, 30], true)) {
            $trendPeriod = 7;
        }

        $salesTotal = (float) Transaction::query()
            ->whereHas('kasir', fn ($query) => $query->where('store_name', $storeName))
            ->sum('total_bayar');
        $purchaseTotal = (float) Purchase::query()
            ->whereHas('pengguna', fn ($query) => $query->where('store_name', $storeName))
            ->sum('total_bayar');
        $productScope = Product::query()->where('store_name', $storeName);
        $stockTotal = (int) (clone $productScope)->sum('stok');
        $productCount = (clone $productScope)->count();
        $stockValue = (float) (clone $productScope)->sum(DB::raw('stok * harga_beli'));
        $activeSuppliers = Supplier::query()
            ->where('store_name', $storeName)
            ->where('is_active', true)
            ->count();
        $criticalProductsCount = (clone $productScope)->whereColumn('stok', '<=', 'stok_minimum')->count();
        $criticalProducts = Product::query()
            ->where('store_name', $storeName)
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderByRaw('(stok_minimum - stok) DESC')
            ->take(5)
            ->get(['id', 'nama_produk', 'kode_produk', 'stok', 'stok_minimum', 'satuan']);

        $forecastHighlights = SalesForecast::query()
            ->with('produk')
            ->whereHas('produk', function ($query) use ($storeName): void {
                $query->where('store_name', $storeName);
            })
            ->latest()
            ->take(5)
            ->get();

        $forecastCount = $forecastHighlights->count();
        $forecastRestockTotal = (int) $forecastHighlights->sum('selisih_prediksi');
        $canManageInventory = $user?->mode_app === 'sederhana';
        $salesTrend = collect(range($trendPeriod - 1, 0))
            ->map(function (int $daysAgo) use ($storeName) {
                $date = Carbon::now()->subDays($daysAgo);

                return [
                    'label' => $date->translatedFormat('d M'),
                    'date' => $date->toDateString(),
                    'total' => (float) Transaction::query()
                        ->whereHas('kasir', fn ($query) => $query->where('store_name', $storeName))
                        ->whereDate('tanggal_transaksi', $date->toDateString())
                        ->sum('total_bayar'),
                ];
            });
        $salesTrendTotal = (float) $salesTrend->sum('total');
        $salesTrendAverage = (float) $salesTrend->avg('total');

        return view('dashboard.index', [
            'isSimpleMode' => $isSimpleMode,
            'salesTotal' => $salesTotal,
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
            'canManageInventory' => $canManageInventory,
            'salesTrend' => $salesTrend,
            'trendPeriod' => $trendPeriod,
            'salesTrendTotal' => $salesTrendTotal,
            'salesTrendAverage' => $salesTrendAverage,
        ]);
    }
}
