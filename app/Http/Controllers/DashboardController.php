<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\SalesForecast;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $isSimpleMode = $user?->mode_app === 'sederhana';

        $salesTotal = (float) Transaction::query()->sum('total_bayar');
        $purchaseTotal = (float) Purchase::query()->sum('total_bayar');
        $stockTotal = (int) Product::query()->sum('stok');
        $productCount = Product::query()->count();
        $stockValue = (float) Product::query()->sum(DB::raw('stok * harga_beli'));
        $activeSuppliers = Supplier::query()->where('is_active', true)->count();
        $criticalProductsCount = Product::query()->whereColumn('stok', '<=', 'stok_minimum')->count();
        $criticalProducts = Product::query()
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->orderByRaw('(stok_minimum - stok) DESC')
            ->take(5)
            ->get(['id', 'nama_produk', 'kode_produk', 'stok', 'stok_minimum', 'satuan']);

        $operationalUsers = User::query()
            ->when(
                $isSimpleMode,
                fn ($query) => $query->whereIn('role', ['owner', 'kasir']),
                fn ($query) => $query->whereIn('role', ['owner', 'kasir', 'gudang']),
            )
            ->latest()
            ->take(5)
            ->get();

        $forecastHighlights = SalesForecast::query()
            ->with('produk')
            ->latest()
            ->take(5)
            ->get();

        $forecastCount = $forecastHighlights->count();
        $forecastRestockTotal = (int) $forecastHighlights->sum('selisih_prediksi');
        $canManageInventory = $user?->mode_app === 'sederhana';

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
            'operationalUsers' => $operationalUsers,
            'forecastHighlights' => $forecastHighlights,
            'forecastCount' => $forecastCount,
            'forecastRestockTotal' => $forecastRestockTotal,
            'canManageInventory' => $canManageInventory,
        ]);
    }
}
