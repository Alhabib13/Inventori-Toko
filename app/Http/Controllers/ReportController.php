<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
        $salesSearch = trim((string) $request->string('sales_search'));
        $purchaseSearch = trim((string) $request->string('purchase_search'));
        $stockSearch = trim((string) $request->string('stock_search'));

        $salesCollection = Transaction::query()
            ->with('kasir')
            ->whereHas('kasir', fn ($query) => $this->scopeToUserStore($query, $user))
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->latest('tanggal_transaksi')
            ->get();

        $purchasesCollection = Purchase::query()
            ->with(['supplier', 'pengguna'])
            ->whereHas('pengguna', fn ($query) => $this->scopeToUserStore($query, $user))
            ->whereBetween('tanggal_pembelian', [$startDate, $endDate])
            ->latest('tanggal_pembelian')
            ->get();

        $stockProductsCollection = Product::query()
            ->with(['kategori', 'supplier'])
            ->tap(fn ($query) => $this->scopeToUserStore($query, $user))
            ->orderBy('nama_produk')
            ->get();

        $salesTotal = (float) $salesCollection->sum('total_bayar');
        $purchaseTotal = (float) $purchasesCollection->sum('total_bayar');
        $stockValue = (float) $stockProductsCollection->sum(
            fn (Product $product) => $product->stok * (float) $product->harga_beli
        );
        $stockLowCount = $stockProductsCollection->filter(fn (Product $product) => $product->stok <= $product->stok_minimum)->count();
        $revenue = $salesTotal;
        $capital = $purchaseTotal;
        $grossProfit = $revenue - $capital;
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0;
        $canViewSalesAndProfit = $user?->role === 'owner';
        $canViewPurchases = $user?->role === 'owner' || ($user?->role === 'gudang' && $user->mode_app === 'lengkap');

        $sales = $this->paginateCollection(
            $salesCollection->filter(function (Transaction $sale) use ($salesSearch) {
                if ($salesSearch === '') {
                    return true;
                }

                return str_contains(strtolower($sale->kode_transaksi), strtolower($salesSearch))
                    || str_contains(strtolower($sale->kasir?->name ?? ''), strtolower($salesSearch))
                    || str_contains(strtolower($sale->status ?? ''), strtolower($salesSearch))
                    || str_contains(strtolower($sale->metode_pembayaran ?? ''), strtolower($salesSearch));
            })->values(),
            10,
            $request,
            'sales_page'
        );

        $purchases = $this->paginateCollection(
            $purchasesCollection->filter(function (Purchase $purchase) use ($purchaseSearch) {
                if ($purchaseSearch === '') {
                    return true;
                }

                return str_contains(strtolower($purchase->kode_pembelian), strtolower($purchaseSearch))
                    || str_contains(strtolower($purchase->supplier?->nama_supplier ?? ''), strtolower($purchaseSearch))
                    || str_contains(strtolower($purchase->pengguna?->name ?? ''), strtolower($purchaseSearch))
                    || str_contains(strtolower($purchase->status ?? ''), strtolower($purchaseSearch));
            })->values(),
            10,
            $request,
            'purchase_page'
        );

        $stockProducts = $this->paginateCollection(
            $stockProductsCollection->filter(function (Product $product) use ($stockSearch) {
                if ($stockSearch === '') {
                    return true;
                }

                return str_contains(strtolower($product->nama_produk), strtolower($stockSearch))
                    || str_contains(strtolower($product->kode_produk), strtolower($stockSearch))
                    || str_contains(strtolower($product->kategori?->nama_kategori ?? ''), strtolower($stockSearch))
                    || str_contains(strtolower($product->supplier?->nama_supplier ?? ''), strtolower($stockSearch));
            })->values(),
            10,
            $request,
            'stock_page'
        );

        $periodLabel = match ($period) {
            '7_hari' => '7 hari terakhir',
            '90_hari' => '90 hari terakhir',
            default => '30 hari terakhir',
        };

        return [
            'isSimpleMode' => $user?->mode_app === 'sederhana',
            'isWarehouseViewer' => $user?->role === 'gudang',
            'period' => $period,
            'periodLabel' => $periodLabel,
            'sales' => $sales,
            'purchases' => $purchases,
            'stockProducts' => $stockProducts,
            'salesAll' => $salesCollection,
            'purchasesAll' => $purchasesCollection,
            'stockProductsAll' => $stockProductsCollection,
            'salesTotal' => $salesTotal,
            'purchaseTotal' => $purchaseTotal,
            'stockValue' => $stockValue,
            'stockLowCount' => $stockLowCount,
            'revenue' => $revenue,
            'capital' => $capital,
            'grossProfit' => $grossProfit,
            'margin' => $margin,
            'canViewSalesAndProfit' => $canViewSalesAndProfit,
            'canViewPurchases' => $canViewPurchases,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesSearch' => $salesSearch,
            'purchaseSearch' => $purchaseSearch,
            'stockSearch' => $stockSearch,
        ];
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request, string $pageName): LengthAwarePaginator
    {
        $page = max((int) $request->query($pageName, 1), 1);
        $total = $items->count();
        $results = $items->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $results,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        );

        return $paginator->appends($request->query());
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

        foreach ($data['salesAll'] as $sale) {
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

        foreach ($data['purchasesAll'] as $purchase) {
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

        foreach ($data['stockProductsAll'] as $product) {
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
