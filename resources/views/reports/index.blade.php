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
        $stockLowCount = $stockProducts->filter(fn ($product) => $product->stok <= $product->stok_minimum)->count();
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
                    <p class="mt-2 text-sm text-slate-500">Selisih sederhana antara pendapatan dan modal pada periode aktif.</p>
                </article>
            @endif
        </section>

        @if ($canViewSalesAndProfit)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Laba Rugi Sederhana</h2>
                        <p class="mt-1 text-sm text-slate-500">Ringkasan pendapatan, modal, keuntungan, dan margin dari periode aktif.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('reports.export', ['section' => 'profit', 'period' => $period]) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                            Export CSV
                        </a>
                        <a href="{{ route('reports.print', ['section' => 'profit', 'period' => $period]) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                            Cetak
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pendapatan</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($revenue, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Modal</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($capital, 0, ',', '.') }}</p>
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
            </section>
        @endif

        @if ($canViewSalesAndProfit)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Laporan Penjualan</h2>
                        <p class="mt-1 text-sm text-slate-500">Transaksi penjualan pada periode {{ $periodLabel }}.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('reports.export', ['section' => 'sales', 'period' => $period]) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                            Export CSV
                        </a>
                        <a href="{{ route('reports.print', ['section' => 'sales', 'period' => $period]) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                            Cetak
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[860px] w-full text-left text-sm">
                        <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Kasir</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Total Item</th>
                                <th class="px-6 py-3">Total</th>
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
                                    <td class="px-6 py-4 text-slate-600">{{ ucfirst($sale->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada transaksi penjualan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('reports.export', ['section' => 'purchases', 'period' => $period]) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                            Export CSV
                        </a>
                        <a href="{{ route('reports.print', ['section' => 'purchases', 'period' => $period]) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                            Cetak
                        </a>
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
            </section>
        @endif

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-[#c0c8cb] px-6 py-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Laporan Stok</h2>
                    <p class="mt-1 text-sm text-slate-500">Kondisi stok produk aktif saat ini.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('reports.export', ['section' => 'stock', 'period' => $period]) }}" class="inline-flex h-10 items-center rounded-lg border border-[#003441]/20 bg-white px-3 text-sm font-semibold text-[#003441] transition hover:bg-[#003441]/5">
                        Export CSV
                    </a>
                    <a href="{{ route('reports.print', ['section' => 'stock', 'period' => $period]) }}" target="_blank" class="inline-flex h-10 items-center rounded-lg bg-[#003441] px-3 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Cetak
                    </a>
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
        </section>
    </div>
@endsection
