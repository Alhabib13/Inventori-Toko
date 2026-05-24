<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', $this->buildReportData($request));
    }

    public function export(Request $request, string $section): StreamedResponse
    {
        $data = $this->buildReportData($request);
        $this->ensureSectionAccess($section, $data);

        $filename = sprintf(
            'laporan-%s-%s.csv',
            $section,
            now()->format('Ymd-His')
        );

        return response()->streamDownload(function () use ($section, $data): void {
            $handle = fopen('php://output', 'w');

            foreach ($this->csvRowsForSection($section, $data) as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function print(Request $request, string $section): View
    {
        $data = $this->buildReportData($request);
        $this->ensureSectionAccess($section, $data);

        return view('reports.print', $data + [
            'section' => $section,
            'sectionTitle' => $this->sectionTitle($section),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request): array
    {
        $user = $request->user();
        $period = $this->resolvePeriod($request);
        $days = $this->periodToDays($period);
        $startDate = now()->startOfDay()->subDays($days - 1);
        $endDate = now()->endOfDay();

        $sales = Transaction::query()
            ->with('kasir')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->latest('tanggal_transaksi')
            ->get();

        $purchases = Purchase::query()
            ->with(['supplier', 'pengguna'])
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->latest('tanggal_pembelian')
            ->get();

        $stockProducts = Product::query()
            ->with(['kategori', 'supplier'])
            ->orderBy('nama_produk')
            ->get();

        $salesTotal = (float) $sales->sum('total_bayar');
        $purchaseTotal = (float) $purchases->sum('total_bayar');
        $stockValue = (float) $stockProducts->sum(
            fn (Product $product) => $product->stok * (float) $product->harga_beli
        );
        $revenue = $salesTotal;
        $capital = $purchaseTotal;
        $grossProfit = $revenue - $capital;
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $canViewSalesAndProfit = $user?->role === 'owner';
        $canViewPurchases = $user?->role === 'owner' || ($user?->role === 'gudang' && $user->mode_app === 'lengkap');

        return [
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
        ];
    }

    private function resolvePeriod(Request $request): string
    {
        return match ($request->string('period')->value()) {
            '7_hari', '30_hari', '90_hari' => $request->string('period')->value(),
            default => '30_hari',
        };
    }

    private function periodToDays(string $period): int
    {
        return match ($period) {
            '7_hari' => 7,
            '90_hari' => 90,
            default => 30,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureSectionAccess(string $section, array $data): void
    {
        $validSections = ['sales', 'purchases', 'stock', 'profit'];

        abort_unless(in_array($section, $validSections, true), 404);

        if (in_array($section, ['sales', 'profit'], true) && ! $data['canViewSalesAndProfit']) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        if ($section === 'purchases' && ! $data['canViewPurchases']) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<int, string|int|float>>
     */
    private function csvRowsForSection(string $section, array $data): array
    {
        return match ($section) {
            'sales' => $this->salesCsvRows($data),
            'purchases' => $this->purchaseCsvRows($data),
            'stock' => $this->stockCsvRows($data),
            'profit' => $this->profitCsvRows($data),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<int, string|int|float>>
     */
    private function salesCsvRows(array $data): array
    {
        $rows = [[
            'Kode Transaksi', 'Kasir', 'Tanggal', 'Total Item', 'Metode Pembayaran', 'Total Bayar', 'Status',
        ]];

        foreach ($data['sales'] as $sale) {
            $rows[] = [
                $sale->kode_transaksi,
                $sale->kasir?->name ?? '-',
                optional($sale->tanggal_transaksi)->format('d/m/Y H:i') ?? '-',
                $sale->total_item,
                $sale->metode_pembayaran ?? '-',
                (float) $sale->total_bayar,
                $sale->status,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<int, string|int|float>>
     */
    private function purchaseCsvRows(array $data): array
    {
        $rows = [[
            'Kode Pembelian', 'Supplier', 'Dicatat Oleh', 'Tanggal', 'Subtotal', 'Diskon', 'Ongkir', 'Total Bayar', 'Status',
        ]];

        foreach ($data['purchases'] as $purchase) {
            $rows[] = [
                $purchase->kode_pembelian,
                $purchase->supplier?->nama_supplier ?? '-',
                $purchase->pengguna?->name ?? '-',
                optional($purchase->tanggal_pembelian)->format('d/m/Y H:i') ?? '-',
                (float) $purchase->subtotal,
                (float) $purchase->diskon,
                (float) $purchase->ongkir,
                (float) $purchase->total_bayar,
                $purchase->status,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<int, string|int|float>>
     */
    private function stockCsvRows(array $data): array
    {
        $rows = [[
            'Kode Produk', 'Nama Produk', 'Kategori', 'Supplier', 'Stok', 'Satuan', 'Stok Minimum', 'Harga Beli', 'Nilai Modal',
        ]];

        foreach ($data['stockProducts'] as $product) {
            $rows[] = [
                $product->kode_produk,
                $product->nama_produk,
                $product->kategori?->nama_kategori ?? '-',
                $product->supplier?->nama_supplier ?? '-',
                $product->stok,
                $product->satuan,
                $product->stok_minimum,
                (float) $product->harga_beli,
                $product->stok * (float) $product->harga_beli,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<int, array<int, string|int|float>>
     */
    private function profitCsvRows(array $data): array
    {
        return [[
            'Periode',
            'Pendapatan',
            'Modal',
            'Keuntungan',
            'Margin (%)',
        ], [
            $data['periodLabel'],
            $data['revenue'],
            $data['capital'],
            $data['grossProfit'],
            round((float) $data['margin'], 2),
        ]];
    }

    private function sectionTitle(string $section): string
    {
        return match ($section) {
            'sales' => 'Laporan Penjualan',
            'purchases' => 'Laporan Pembelian',
            'stock' => 'Laporan Stok',
            'profit' => 'Laba Rugi Sederhana',
        };
    }
}
