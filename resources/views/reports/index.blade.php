@extends('layouts.app')

@section('page_title', 'Laporan')
@section('page_subtitle', $isWarehouseViewer
    ? 'Pantau laporan stok dan pembelian untuk kebutuhan operasional gudang.'
    : ($isSimpleMode
        ? 'Pantau laporan penjualan, ringkasan stok, barang terlaris, dan filter tanggal untuk owner mode sederhana.'
        : 'Pantau laporan penjualan, pembelian, stok, filter periode, dan ringkasan performa bisnis owner mode lengkap.'))

@section('page_actions')
    <form method="GET" class="flex items-center gap-3">
        <select name="period" class="h-11 rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
            <option value="7_hari" @selected($period === '7_hari')>7 hari terakhir</option>
            <option value="30_hari" @selected($period === '30_hari')>30 hari terakhir</option>
            <option value="90_hari" @selected($period === '90_hari')>90 hari terakhir</option>
        </select>
        <button type="submit" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Terapkan Filter
        </button>
    </form>
@endsection

@section('content')
    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @if ($canViewSalesAndProfit)
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Laporan Penjualan</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($salesTotal, 0, ',', '.') }}</h2>
                    <p class="mt-2 text-sm text-slate-500">Total penjualan untuk {{ $periodLabel }}.</p>
                </article>
            @endif
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $canViewPurchases ? 'Laporan Pembelian' : 'Ringkasan Stok' }}</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($canViewPurchases ? $purchaseTotal : $stockValue, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $canViewPurchases ? 'Total pembelian supplier pada periode terpilih.' : 'Estimasi nilai stok aktif untuk pemantauan owner sederhana.' }}</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Laporan Stok</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($stockValue, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Estimasi nilai modal stok aktif saat ini.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $canViewSalesAndProfit ? ($isSimpleMode ? 'Filter Tanggal' : 'Performa Bisnis') : 'Periode Aktif' }}</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $canViewSalesAndProfit ? number_format($margin, 1, ',', '.').'%' : ucfirst(str_replace('_', ' ', $periodLabel)) }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $canViewSalesAndProfit ? ($isSimpleMode ? 'Pantau perubahan performa sederhana berdasarkan periode yang dipilih.' : 'Margin kasar antara penjualan dan pembelian.') : 'Laporan gudang difokuskan pada pembelian dan kondisi stok untuk periode terpilih.' }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            @if ($canViewSalesAndProfit)
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Laba Rugi Sederhana</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <dl class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pendapatan</dt>
                                    <dd class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($revenue, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Modal</dt>
                                    <dd class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($capital, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Keuntungan</dt>
                                    <dd class="mt-2 text-xl font-bold {{ $grossProfit >= 0 ? 'text-emerald-700' : 'text-red-600' }}">Rp{{ number_format($grossProfit, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <p class="text-sm font-semibold text-slate-900">Rekomendasi Owner</p>
                            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                                @if ($isSimpleMode)
                                    <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Periksa barang terlaris untuk memastikan stok aman selama periode berjalan.</span></li>
                                    <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Gunakan prediksi sederhana untuk menentukan restock tanpa analisis terlalu dalam.</span></li>
                                    <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Evaluasi stok minimum agar produk cepat laku tidak sering kosong.</span></li>
                                @else
                                    <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Fokuskan pembelian ulang pada produk dengan stok kritis dan penjualan cepat.</span></li>
                                    <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Tinjau supplier dengan nilai pembelian tertinggi untuk negosiasi harga.</span></li>
                                    <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Pantau korelasi tren penjualan dengan prediksi stok agar pengadaan lebih presisi.</span></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </article>
            @else
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Ringkasan Gudang</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <dl class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Pembelian</dt>
                                    <dd class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($purchaseTotal, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nilai Modal Stok</dt>
                                    <dd class="mt-2 text-xl font-bold text-slate-900">Rp{{ number_format($stockValue, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <p class="text-sm font-semibold text-slate-900">Catatan Operasional</p>
                            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                                <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Pantau stok kritis dan jadwalkan pembelian ulang lebih awal.</span></li>
                                <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Gunakan laporan pembelian untuk memeriksa pemasok dan nilai modal masuk.</span></li>
                            </ul>
                        </div>
                    </div>
                </article>
            @endif

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Filter Tanggal Aktif' : 'Filter Periode Aktif' }}</h2>
                <div class="mt-5 space-y-4">
                    <div class="rounded-xl border border-[#003441]/10 bg-[#d0e1fb]/25 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#0f4c5c]">Periode</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ ucfirst(str_replace('_', ' ', $periodLabel)) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-sm font-semibold text-slate-900">Catatan</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $isSimpleMode
                                ? 'Mode sederhana menonjolkan laporan yang cepat dibaca owner: penjualan, ringkasan stok, barang terlaris, dan filter tanggal.'
                                : 'Halaman ini disiapkan untuk owner lengkap agar evaluasi performa bisnis bisa dilakukan dari satu tempat sebelum turun ke modul stok, supplier, atau prediksi.' }}
                        </p>
                    </div>
                </div>
            </article>
        </section>

        @if ($canViewSalesAndProfit)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="border-b border-[#c0c8cb] px-6 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
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
                <div class="border-b border-[#c0c8cb] px-6 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
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
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
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
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            <th class="px-6 py-3">Supplier</th>
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
                                <td class="px-6 py-4 text-slate-600">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $product->stok }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">Rp{{ number_format($product->stok * (float) $product->harga_beli, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada data stok produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if ($canViewSalesAndProfit)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="border-b border-[#c0c8cb] px-6 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Export atau Cetak Laba Rugi Sederhana</h2>
                            <p class="mt-1 text-sm text-slate-500">Gunakan ringkasan laba rugi untuk evaluasi performa bisnis pada periode {{ $periodLabel }}.</p>
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
                </div>
                <div class="px-6 py-4 text-sm text-slate-600">
                    Ringkasan ini memakai data penjualan dan pembelian pada periode aktif.
                </div>
            </section>
        @endif
    </div>
@endsection
