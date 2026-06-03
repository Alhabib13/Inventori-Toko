@extends('layouts.app')

@section('page_title', 'Laporan')
@section('page_subtitle', $isWarehouseViewer
    ? 'Pantau laporan pembelian dan stok yang relevan untuk operasional gudang.'
    : ($isSimpleMode
        ? 'Pantau penjualan, pembelian, nilai stok, stok menipis, dan laba rugi sederhana untuk owner mode sederhana.'
        : 'Pantau penjualan, pembelian, nilai stok, stok menipis, dan laba rugi sederhana untuk owner mode lengkap.'))

@section('page_actions')
    <form method="GET" class="flex flex-wrap items-center gap-3">
        <div class="space-y-2">
            <label for="report_period" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode</label>
            <select id="report_period" name="period" class="h-11 rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
                <option value="7_hari" @selected($period === '7_hari')>7 hari terakhir</option>
                <option value="30_hari" @selected($period === '30_hari')>30 hari terakhir</option>
                <option value="90_hari" @selected($period === '90_hari')>90 hari terakhir</option>
            </select>
        </div>
        <button type="submit" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Terapkan Filter
        </button>
    </form>
@endsection

@section('content')
    @php
        $stockLowCount = $stockLowCount ?? $stockProducts->getCollection()->filter(fn ($product) => $product->stok <= $product->stok_minimum)->count();
        $salesSearch = $salesSearch ?? '';
        $purchaseSearch = $purchaseSearch ?? '';
        $stockSearch = $stockSearch ?? '';
        $activeReportQuery = array_filter([
            'period' => $period,
            'sales_search' => $salesSearch,
            'purchase_search' => $purchaseSearch,
            'stock_search' => $stockSearch,
        ], fn ($value) => filled($value));
        $activeFilterBadges = array_filter([
            'Periode: '.ucfirst(str_replace('_', ' ', $periodLabel)),
            $salesSearch !== '' ? 'Penjualan: '.$salesSearch : null,
            $purchaseSearch !== '' ? 'Pembelian: '.$purchaseSearch : null,
            $stockSearch !== '' ? 'Stok: '.$stockSearch : null,
        ]);

        $paginationWindow = function ($paginator) {
            $lastPage = $paginator->lastPage();
            $currentPage = $paginator->currentPage();
            $startPage = max(1, min($currentPage - 1, max(1, $lastPage - 2)));
            $endPage = min($lastPage, $startPage + 2);

            return [$startPage, $endPage];
        };
        [$salesStartPage, $salesEndPage] = $paginationWindow($sales);
        [$purchaseStartPage, $purchaseEndPage] = $paginationWindow($purchases);
        [$stockStartPage, $stockEndPage] = $paginationWindow($stockProducts);
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isWarehouseViewer ? 'Laporan Gudang' : 'Laporan Owner' }}</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">
                        {{ $isWarehouseViewer ? 'Filter periode dan tabel laporan gudang dibuat lebih jelas untuk stok dan pembelian.' : 'Ringkasan angka dan tabel laporan dibuat lebih terstruktur agar mudah dibaca owner.' }}
                    </h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                        {{ $isWarehouseViewer
                            ? 'Gudang hanya melihat bagian laporan yang relevan dengan stok dan pembelian, sehingga evaluasi operasional harian tetap fokus.'
                            : 'Owner melihat ringkasan utama di bagian atas, lalu tabel penjualan, pembelian, stok, dan laba rugi sederhana pada satu halaman yang tetap rapi saat data banyak atau kosong.' }}
                    </p>
                    <div class="mt-5 rounded-2xl border border-[#d7dfe3] bg-[#f9f9fa] p-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Filter Aktif</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($activeFilterBadges as $badge)
                                        <span class="inline-flex rounded-full border border-[#003441]/15 bg-white px-3 py-1 text-xs font-semibold text-[#003441]">
                                            {{ $badge }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            <p class="max-w-md text-sm leading-6 text-slate-500">
                                Tombol export dan cetak memakai periode serta pencarian aktif, jadi hasil file mengikuti data yang sedang tampil.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Aktif</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ ucfirst(str_replace('_', ' ', $periodLabel)) }}</p>
                        <p class="mt-2 text-sm text-slate-500">Gunakan filter di atas untuk mengubah cakupan laporan.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isWarehouseViewer ? 'Produk Stok Menipis' : 'Margin Laba' }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $isWarehouseViewer ? $stockLowCount : number_format($margin, 1, ',', '.').'%' }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $isWarehouseViewer ? 'Produk yang perlu dipantau segera oleh gudang.' : 'Ringkasan performa sederhana dari periode yang dipilih.' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @if ($canViewSalesAndProfit)
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Penjualan</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($salesTotal, 0, ',', '.') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Total penjualan untuk {{ $periodLabel }}.</p>
                </article>
            @endif

            @if ($canViewPurchases)
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pembelian</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($purchaseTotal, 0, ',', '.') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Total pembelian supplier pada periode terpilih.</p>
                </article>
            @endif

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nilai Stok</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($stockValue, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Estimasi nilai modal stok aktif saat ini.</p>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Menipis</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $stockLowCount }}</h2>
                <p class="mt-2 text-sm text-slate-500">Produk yang sudah berada di bawah atau sama dengan stok minimum.</p>
            </article>

            @if ($canViewSalesAndProfit)
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm md:col-span-2 xl:col-span-1">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Laba Rugi Sederhana</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight {{ $grossProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">Rp{{ number_format($grossProfit, 0, ',', '.') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Selisih antara omzet penjualan dan modal barang yang benar-benar terjual.</p>
                </article>
            @endif
        </section>

        @if ($canViewSalesAndProfit)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Laba Rugi Sederhana</h2>
                        <p class="mt-1 text-sm text-slate-500">Ringkasan omzet penjualan, modal barang terjual, keuntungan, dan margin dari periode aktif.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="flex basis-full items-center text-xs font-semibold text-slate-500 md:basis-auto">
                            Mengikuti filter aktif
                        </span>
                        <a href="{{ route('reports.export', array_merge(['section' => 'profit'], $activeReportQuery)) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                            Export CSV
                        </a>
                        <a href="{{ route('reports.print', array_merge(['section' => 'profit'], $activeReportQuery)) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                            Cetak
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 border-b border-slate-200 p-6 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Omzet Penjualan</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($revenue, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">Omzet penjualan.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Modal Barang Terjual</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($capital, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-slate-500">HPP barang terjual.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Keuntungan</p>
                        <p class="mt-2 text-xl font-bold {{ $grossProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">Rp{{ number_format($grossProfit, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Margin</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ number_format($margin, 1, ',', '.') }}%</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full text-left text-sm">
                        <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Periode</th>
                                <th class="px-6 py-3">Omzet Penjualan</th>
                                <th class="px-6 py-3">Modal Barang Terjual</th>
                                <th class="px-6 py-3">Keuntungan</th>
                                <th class="px-6 py-3">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $periodLabel }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">Rp{{ number_format((float) $revenue, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-slate-600">Rp{{ number_format((float) $capital, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 font-semibold {{ $grossProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">Rp{{ number_format((float) $grossProfit, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ number_format((float) $margin, 1, ',', '.') }}%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if ($canViewSalesAndProfit)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Laporan Penjualan</h2>
                        <p class="mt-1 text-sm text-slate-500">Transaksi penjualan pada periode {{ $periodLabel }}.</p>
                    </div>
                    <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[26rem] lg:flex-row lg:items-center lg:justify-end">
                        <form method="GET" class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:flex-1">
                            <input type="hidden" name="period" value="{{ $period }}">
                            @if ($purchaseSearch !== '')<input type="hidden" name="purchase_search" value="{{ $purchaseSearch }}">@endif
                            @if ($stockSearch !== '')<input type="hidden" name="stock_search" value="{{ $stockSearch }}">@endif
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                </span>
                                <input type="search" name="sales_search" value="{{ $salesSearch }}" placeholder="Cari penjualan..." class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]">
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex h-10 items-center rounded-xl border border-[#003441]/20 bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">Cari</button>
                                @if ($salesSearch !== '')
                                    <a href="{{ route('reports.index', array_filter(['period' => $period, 'purchase_search' => $purchaseSearch, 'stock_search' => $stockSearch])) }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Reset</a>
                                @endif
                            </div>
                        </form>
                        <div class="flex flex-wrap gap-2">
                            <span class="flex basis-full items-center text-xs font-semibold text-slate-500 sm:basis-auto">
                                Mengikuti filter aktif
                            </span>
                            <a href="{{ route('reports.export', array_merge(['section' => 'sales'], $activeReportQuery)) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                                Export CSV
                            </a>
                            <a href="{{ route('reports.print', array_merge(['section' => 'sales'], $activeReportQuery)) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                Cetak
                            </a>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[1040px] w-full text-left text-sm">
                        <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Kasir</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Total Item</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Modal Barang Terjual</th>
                                <th class="px-6 py-3">Keuntungan</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($sales as $sale)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $sale->kode_transaksi }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $sale->kasir?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $sale->tanggal_transaksi?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $sale->total_item }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">Rp{{ number_format((float) $sale->total_bayar, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-slate-600">Rp{{ number_format((float) $sale->modal_barang_terjual, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 font-semibold {{ (float) $sale->keuntungan_penjualan >= 0 ? 'text-emerald-700' : 'text-red-600' }}">Rp{{ number_format((float) $sale->keuntungan_penjualan, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ ucfirst($sale->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-slate-500">Belum ada transaksi penjualan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-6 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="text-sm text-slate-500">
                            Menampilkan
                            <span class="font-semibold text-slate-700">{{ $sales->firstItem() ?? 0 }}</span>
                            -
                            <span class="font-semibold text-slate-700">{{ $sales->lastItem() ?? 0 }}</span>
                            dari
                            <span class="font-semibold text-slate-700">{{ $sales->total() }}</span>
                            transaksi
                        </div>
                    </div>
                    @if ($sales->lastPage() > 1)
                        <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <form method="GET" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                <input type="hidden" name="period" value="{{ $period }}">
                                @if ($salesSearch !== '')<input type="hidden" name="sales_search" value="{{ $salesSearch }}">@endif
                                @if ($purchaseSearch !== '')<input type="hidden" name="purchase_search" value="{{ $purchaseSearch }}">@endif
                                @if ($stockSearch !== '')<input type="hidden" name="stock_search" value="{{ $stockSearch }}">@endif
                                <span>Lompat ke halaman</span>
                                <input type="number" name="sales_page" min="1" max="{{ $sales->lastPage() }}" value="{{ $sales->currentPage() }}" class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]">
                                <span>dari {{ $sales->lastPage() }}</span>
                                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka</button>
                            </form>
                            <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                                @if ($sales->onFirstPage())
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Sebelumnya</span>
                                @else
                                    <a href="{{ $sales->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sebelumnya</a>
                                @endif
                                @for ($page = $salesStartPage; $page <= $salesEndPage; $page++)
                                    @if ($page === $sales->currentPage())
                                        <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">{{ $page }}</span>
                                    @else
                                        <a href="{{ $sales->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">{{ $page }}</a>
                                    @endif
                                @endfor
                                @if ($sales->hasMorePages())
                                    <a href="{{ $sales->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Berikutnya</a>
                                @else
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Berikutnya</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($canViewPurchases)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Laporan Pembelian</h2>
                        <p class="mt-1 text-sm text-slate-500">Pembelian supplier pada periode {{ $periodLabel }}.</p>
                    </div>
                    <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[26rem] lg:flex-row lg:items-center lg:justify-end">
                        <form method="GET" class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:flex-1">
                            <input type="hidden" name="period" value="{{ $period }}">
                            @if ($salesSearch !== '')<input type="hidden" name="sales_search" value="{{ $salesSearch }}">@endif
                            @if ($stockSearch !== '')<input type="hidden" name="stock_search" value="{{ $stockSearch }}">@endif
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                </span>
                                <input type="search" name="purchase_search" value="{{ $purchaseSearch }}" placeholder="Cari pembelian..." class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]">
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex h-10 items-center rounded-xl border border-[#003441]/20 bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">Cari</button>
                                @if ($purchaseSearch !== '')
                                    <a href="{{ route('reports.index', array_filter(['period' => $period, 'sales_search' => $salesSearch, 'stock_search' => $stockSearch])) }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Reset</a>
                                @endif
                            </div>
                        </form>
                        <div class="flex flex-wrap gap-2">
                            <span class="flex basis-full items-center text-xs font-semibold text-slate-500 sm:basis-auto">
                                Mengikuti filter aktif
                            </span>
                            <a href="{{ route('reports.export', array_merge(['section' => 'purchases'], $activeReportQuery)) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                                Export CSV
                            </a>
                            <a href="{{ route('reports.print', array_merge(['section' => 'purchases'], $activeReportQuery)) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                Cetak
                            </a>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[860px] w-full text-left text-sm">
                        <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Supplier</th>
                                <th class="px-6 py-3">Dicatat Oleh</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Total</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($purchases as $purchase)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $purchase->kode_pembelian }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $purchase->supplier?->nama_supplier ?? '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $purchase->pengguna?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $purchase->tanggal_pembelian?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ ucfirst($purchase->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada pembelian pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-200 px-6 py-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="text-sm text-slate-500">
                            Menampilkan
                            <span class="font-semibold text-slate-700">{{ $purchases->firstItem() ?? 0 }}</span>
                            -
                            <span class="font-semibold text-slate-700">{{ $purchases->lastItem() ?? 0 }}</span>
                            dari
                            <span class="font-semibold text-slate-700">{{ $purchases->total() }}</span>
                            pembelian
                        </div>
                    </div>
                    @if ($purchases->lastPage() > 1)
                        <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <form method="GET" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                <input type="hidden" name="period" value="{{ $period }}">
                                @if ($salesSearch !== '')<input type="hidden" name="sales_search" value="{{ $salesSearch }}">@endif
                                @if ($purchaseSearch !== '')<input type="hidden" name="purchase_search" value="{{ $purchaseSearch }}">@endif
                                @if ($stockSearch !== '')<input type="hidden" name="stock_search" value="{{ $stockSearch }}">@endif
                                <span>Lompat ke halaman</span>
                                <input type="number" name="purchase_page" min="1" max="{{ $purchases->lastPage() }}" value="{{ $purchases->currentPage() }}" class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]">
                                <span>dari {{ $purchases->lastPage() }}</span>
                                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka</button>
                            </form>
                            <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                                @if ($purchases->onFirstPage())
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Sebelumnya</span>
                                @else
                                    <a href="{{ $purchases->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sebelumnya</a>
                                @endif
                                @for ($page = $purchaseStartPage; $page <= $purchaseEndPage; $page++)
                                    @if ($page === $purchases->currentPage())
                                        <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">{{ $page }}</span>
                                    @else
                                        <a href="{{ $purchases->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">{{ $page }}</a>
                                    @endif
                                @endfor
                                @if ($purchases->hasMorePages())
                                    <a href="{{ $purchases->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Berikutnya</a>
                                @else
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Berikutnya</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Laporan Stok</h2>
                    <p class="mt-1 text-sm text-slate-500">Kondisi stok produk aktif saat ini.</p>
                </div>
                <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[26rem] lg:flex-row lg:items-center lg:justify-end">
                    <form method="GET" class="flex w-full flex-col gap-3 sm:flex-row sm:items-center lg:w-auto lg:flex-1">
                        <input type="hidden" name="period" value="{{ $period }}">
                        @if ($salesSearch !== '')<input type="hidden" name="sales_search" value="{{ $salesSearch }}">@endif
                        @if ($purchaseSearch !== '')<input type="hidden" name="purchase_search" value="{{ $purchaseSearch }}">@endif
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </span>
                            <input type="search" name="stock_search" value="{{ $stockSearch }}" placeholder="Cari stok..." class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex h-10 items-center rounded-xl border border-[#003441]/20 bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">Cari</button>
                            @if ($stockSearch !== '')
                                <a href="{{ route('reports.index', array_filter(['period' => $period, 'sales_search' => $salesSearch, 'purchase_search' => $purchaseSearch])) }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Reset</a>
                            @endif
                        </div>
                    </form>
                    <div class="flex flex-wrap gap-2">
                        <span class="flex basis-full items-center text-xs font-semibold text-slate-500 sm:basis-auto">
                            Mengikuti filter aktif
                        </span>
                        <a href="{{ route('reports.export', array_merge(['section' => 'stock'], $activeReportQuery)) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                            Export CSV
                        </a>
                        <a href="{{ route('reports.print', array_merge(['section' => 'stock'], $activeReportQuery)) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                            Cetak
                        </a>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            @if (! $isWarehouseViewer)
                                <th class="px-6 py-3">Supplier</th>
                            @endif
                            <th class="px-6 py-3">Stok</th>
                            <th class="px-6 py-3">Min.</th>
                            <th class="px-6 py-3">Nilai Modal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($stockProducts as $product)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $product->kode_produk }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                                @if (! $isWarehouseViewer)
                                    <td class="px-6 py-4 text-slate-600">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                                @endif
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $product->stok }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">Rp{{ number_format($product->stok * (float) $product->harga_beli, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isWarehouseViewer ? '5' : '6' }}" class="px-6 py-8 text-center text-slate-500">Belum ada data stok produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm text-slate-500">
                        Menampilkan
                        <span class="font-semibold text-slate-700">{{ $stockProducts->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold text-slate-700">{{ $stockProducts->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-semibold text-slate-700">{{ $stockProducts->total() }}</span>
                        stok
                    </div>
                </div>
                @if ($stockProducts->lastPage() > 1)
                    <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <form method="GET" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                            <input type="hidden" name="period" value="{{ $period }}">
                            @if ($salesSearch !== '')<input type="hidden" name="sales_search" value="{{ $salesSearch }}">@endif
                            @if ($purchaseSearch !== '')<input type="hidden" name="purchase_search" value="{{ $purchaseSearch }}">@endif
                            @if ($stockSearch !== '')<input type="hidden" name="stock_search" value="{{ $stockSearch }}">@endif
                            <span>Lompat ke halaman</span>
                            <input type="number" name="stock_page" min="1" max="{{ $stockProducts->lastPage() }}" value="{{ $stockProducts->currentPage() }}" class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]">
                            <span>dari {{ $stockProducts->lastPage() }}</span>
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka</button>
                        </form>
                        <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                            @if ($stockProducts->onFirstPage())
                                <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Sebelumnya</span>
                            @else
                                <a href="{{ $stockProducts->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sebelumnya</a>
                            @endif
                            @for ($page = $stockStartPage; $page <= $stockEndPage; $page++)
                                @if ($page === $stockProducts->currentPage())
                                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">{{ $page }}</span>
                                @else
                                    <a href="{{ $stockProducts->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">{{ $page }}</a>
                                @endif
                            @endfor
                            @if ($stockProducts->hasMorePages())
                                <a href="{{ $stockProducts->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Berikutnya</a>
                            @else
                                <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Berikutnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
