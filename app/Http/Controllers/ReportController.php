<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $period = $request->string('period')->value() ?: '30_hari';
        $days = match ($period) {
            '7_hari' => 7,
            '90_hari' => 90,
            default => 30,
        };

        $startDate = now()->startOfDay()->subDays($days - 1);
        $endDate = now()->endOfDay();

        $salesQuery = Transaction::query()
            ->with('kasir')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->latest('tanggal_transaksi');

        $purchasesQuery = Purchase::query()
            ->with(['supplier', 'pengguna'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->latest('tanggal_pembelian');

        $sales = $salesQuery->get();
        $purchases = $purchasesQuery->get();
        $stockProducts = Product::query()
            ->with(['kategori', 'supplier'])
            ->orderBy('nama_produk')
            ->get();

        $salesTotal = (float) $sales->sum('total_bayar');
        $purchaseTotal = (float) $purchases->sum('total_bayar');
        $stockValue = (float) $stockProducts->sum(fn (Product $product) => $product->stok * (float) $product->harga_beli);
        $revenue = $salesTotal;
        $capital = $purchaseTotal;
        $grossProfit = $revenue - $capital;
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $canViewSalesAndProfit = $user?->role === 'owner';
        $canViewPurchases = $user?->role === 'owner' || ($user?->role === 'gudang' && $user->mode_app === 'lengkap');

        return view('reports.index', [
            'isSimpleMode' => $user?->mode_app === 'sederhana',
            'isWarehouseViewer' => $user?->role === 'gudang',
            'period' => $period,
            'periodLabel' => match ($period) {
                '7_hari' => '7 hari terakhir',
                '90_hari' => '90 hari terakhir',
                default => '30 hari terakhir',
            },
            'sales' => $sales,
            'purchases' => $purchases,
            'stockProducts' => $stockProducts,
            'salesTotal' => $salesTotal,
            'purchaseTotal' => $purchaseTotal,
            'stockValue' => $stockValue,
            'revenue' => $revenue,
            'capital' => $capital,
            'grossProfit' => $grossProfit,
            'margin' => $margin,
            'canViewSalesAndProfit' => $canViewSalesAndProfit,
            'canViewPurchases' => $canViewPurchases,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
