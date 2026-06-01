@extends('layouts.app')

@section('page_title', 'Dashboard Overview')
@section('page_subtitle', $isSimpleMode
    ? 'Ringkasan operasional toko sederhana untuk memantau keuntungan, pembelian, nilai stok, stok menipis, dan prediksi restock.'
    : 'Ringkasan monitoring bisnis dan inventori untuk memantau keuntungan, pembelian, nilai stok, stok menipis, dan prediksi stok.')

@section('page_actions')
    @if ($isSimpleMode)
        <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
            Produk Baru
        </a>
    @else
        <a href="{{ route('reports.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
            Ekspor Laporan
        </a>
    @endif
    <a href="{{ route('forecasts.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Lihat Prediksi
    </a>
@endsection

@section('content')
    @php
        $trendMax = max(1, (int) $salesTrend->max('total'));
        $firstPoint = $salesTrend->first();
        $lastPoint = $salesTrend->last();
        $chartPoints = $salesTrend
            ->values()
            ->map(function ($point, $index) use ($salesTrend, $trendMax) {
                $width = 100;
                $height = 100;
                $x = $salesTrend->count() === 1 ? 50 : ($index * ($width / max(1, $salesTrend->count() - 1)));
                $y = $height - (($point['total'] / $trendMax) * 84) - 8;

                return round($x, 2).','.round($y, 2);
            })
            ->implode(' ');
        $chartAreaPoints = '0,92 '.$chartPoints.' 100,92';
        $forecastAvailable = $forecastHighlights->isNotEmpty();
    @endphp

    <div class="space-y-6">
        <section class="rounded-3xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isSimpleMode ? 'Owner Mode Sederhana' : 'Owner Mode Lengkap' }}</p>
                    <h2 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight text-slate-900">
                        {{ $isSimpleMode ? 'Pantau operasional toko harian tanpa kehilangan fokus pada stok dan penjualan.' : 'Pantau performa bisnis dan inventori toko secara menyeluruh dalam satu dashboard.' }}
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                        {{ $isSimpleMode
                            ? 'Dashboard ini dirapikan untuk menonjolkan data operasional utama toko: estimasi keuntungan, pembelian barang, nilai stok berjalan, stok menipis, dan sinyal restock dari prediksi.'
                            : 'Dashboard ini dirapikan untuk menonjolkan monitoring bisnis dan inventori: estimasi keuntungan, pembelian, nilai stok aktif, produk yang perlu perhatian, serta prediksi stok yang tersedia.' }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jumlah Produk</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ number_format($productCount, 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $isSimpleMode ? 'Jumlah produk yang dipakai untuk operasional toko.' : 'Jumlah produk aktif yang masih dipantau owner lengkap.' }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isSimpleMode ? 'Prediksi Tersedia' : 'Supplier Aktif' }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $isSimpleMode ? $forecastCount : $activeSuppliers }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $isSimpleMode ? 'Jumlah data prediksi yang siap membantu restock.' : 'Supplier yang masih aktif mendukung pembelian toko.' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Estimasi Keuntungan</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($grossProfitTotal, 0, ',', '.') }}</h3>
                <p class="mt-3 text-sm text-slate-500">Estimasi laba kotor dari item terjual. Omzet tercatat Rp{{ number_format($salesTotal, 0, ',', '.') }}.</p>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Pembelian</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($purchaseTotal, 0, ',', '.') }}</h3>
                <p class="mt-3 text-sm text-slate-500">{{ $isSimpleMode ? 'Belanja barang masuk yang mendukung stok operasional toko.' : 'Belanja supplier yang telah diproses pada inventori aktif.' }}</p>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nilai Stok</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($stockValue, 0, ',', '.') }}</h3>
                <p class="mt-3 text-sm text-slate-500">{{ $isSimpleMode ? 'Estimasi nilai modal stok aktif untuk operasional sederhana.' : 'Estimasi nilai modal stok aktif yang sedang dimonitor owner lengkap.' }}</p>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Menipis</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($criticalProductsCount, 0, ',', '.') }}</h3>
                <p class="mt-3 text-sm text-slate-500">{{ $isSimpleMode ? 'Produk yang perlu diprioritaskan agar transaksi tetap lancar.' : 'Produk yang perlu dipantau karena sudah mendekati batas minimum.' }}</p>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Prediksi Stok</p>
                <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $forecastAvailable ? $forecastCount : 0 }}</h3>
                <p class="mt-3 text-sm text-slate-500">{{ $forecastAvailable ? 'Data prediksi tersedia untuk membantu keputusan restock.' : 'Belum ada data prediksi stok yang bisa ditampilkan.' }}</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-[#c0c8cb] px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Tren Penjualan {{ $trendPeriod }} Hari</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Pantau ritme penjualan harian untuk membantu keputusan operasional toko.' : 'Pantau ritme penjualan harian untuk membaca performa bisnis dan kebutuhan inventori.' }}</p>
                    </div>
                    <div class="inline-flex rounded-lg border border-[#c0c8cb] bg-white p-1">
                        <a href="{{ route('dashboard.index', ['trend' => 7]) }}" class="inline-flex h-9 items-center rounded-md px-3 text-sm font-semibold transition {{ $trendPeriod === 7 ? 'bg-[#003441] text-white' : 'text-slate-600 hover:bg-[#f3f4f5]' }}">
                            7 Hari
                        </a>
                        <a href="{{ route('dashboard.index', ['trend' => 30]) }}" class="inline-flex h-9 items-center rounded-md px-3 text-sm font-semibold transition {{ $trendPeriod === 30 ? 'bg-[#003441] text-white' : 'text-slate-600 hover:bg-[#f3f4f5]' }}">
                            30 Hari
                        </a>
                    </div>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Dipilih</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $trendPeriod }} Hari Terakhir</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Keuntungan</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format($salesTrendProfitTotal, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Rata-Rata Keuntungan</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format($salesTrendProfitAverage, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <svg viewBox="0 0 100 100" class="h-64 w-full">
                            <line x1="0" y1="92" x2="100" y2="92" stroke="#d6dde1" stroke-width="1" />
                            <line x1="0" y1="50" x2="100" y2="50" stroke="#ecf0f2" stroke-width="1" stroke-dasharray="3 3" />
                            <line x1="0" y1="8" x2="100" y2="8" stroke="#ecf0f2" stroke-width="1" stroke-dasharray="3 3" />
                            <polygon points="{{ $chartAreaPoints }}" fill="#d0e1fb" opacity="0.45" />
                            <polyline fill="none" stroke="#0f4c5c" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="{{ $chartPoints }}" />
                            @foreach ($salesTrend as $pointIndex => $point)
                                @php
                                    $x = $salesTrend->count() === 1 ? 50 : ($pointIndex * (100 / max(1, $salesTrend->count() - 1)));
                                    $y = 100 - (($point['total'] / $trendMax) * 84) - 8;
                                @endphp
                                <circle cx="{{ round($x, 2) }}" cy="{{ round($y, 2) }}" r="2.2" fill="#003441" />
                            @endforeach
                        </svg>
                        <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $firstPoint['label'] ?? '-' }}</span>
                            <span>Puncak Rp{{ number_format($trendMax, 0, ',', '.') }}</span>
                            <span>{{ $lastPoint['label'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </article>

            <article class="space-y-6">
                <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Prioritas Hari Ini' : 'Insight Inventori' }}</h2>
                    <div class="mt-5 space-y-4">
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            @if ($isSimpleMode)
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Menipis</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $criticalProductsCount }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Produk perlu dicek lebih dulu.</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Restock Disarankan</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $forecastRestockTotal }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Unit tambahan dari prediksi stok.</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Transaksi Hari Ini</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $todaySalesCount }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Transaksi selesai yang sudah tercatat.</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Keuntungan Hari Ini</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">Rp{{ number_format($todayGrossProfit, 0, ',', '.') }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Omzet hari ini Rp{{ number_format($todaySalesTotal, 0, ',', '.') }}.</p>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Kritis</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $criticalProductsCount }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Butuh perhatian segera.</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Gap Restock</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $criticalStockGapTotal }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Unit minimum yang perlu ditutup.</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Forecast Restock</p>
                                        <p class="mt-2 text-lg font-semibold text-slate-900">{{ $forecastRestockCount }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Produk dengan sinyal restock.</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pembelian Terakhir</p>
                                        @if ($latestPurchase)
                                            <p class="mt-2 text-sm font-semibold text-slate-900">{{ $latestPurchase->tanggal_pembelian?->format('d M Y') }}</p>
                                            <p class="mt-1 text-xs text-slate-500">Rp{{ number_format((float) $latestPurchase->total_bayar, 0, ',', '.') }}</p>
                                        @else
                                            <p class="mt-2 text-sm font-semibold text-slate-900">Belum ada</p>
                                            <p class="mt-1 text-xs text-slate-500">Belum ada pembelian tercatat.</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Prediksi Restock</p>
                            @if ($forecastAvailable)
                                <div class="mt-3 space-y-3">
                                    @foreach ($forecastHighlights->take(3) as $forecast)
                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ $forecast->produk?->nama_produk ?? 'Produk tidak ditemukan' }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">Prediksi {{ $forecast->prediksi_stok }} {{ $forecast->produk?->satuan ?? '' }}, stok {{ $forecast->stok_aktual }}</p>
                                                </div>
                                                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ $forecast->selisih_prediksi > 0 ? 'bg-red-50 text-red-600' : 'bg-[#d0e1fb]/40 text-[#0f4c5c]' }}">
                                                    {{ $forecast->selisih_prediksi > 0 ? 'Restock '.$forecast->selisih_prediksi : 'Aman' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    Belum ada data prediksi stok. Gunakan modul prediksi untuk menampilkan rekomendasi restock.
                                </p>
                            @endif
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Stok Menipis</p>
                            @if ($criticalProducts->isNotEmpty())
                                <div class="mt-3 space-y-3">
                                    @foreach ($criticalProducts as $product)
                                        <div class="rounded-lg border border-slate-200 bg-white px-3 py-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">Stok {{ $product->stok }} {{ $product->satuan }}, minimum {{ $product->stok_minimum }}</p>
                                                </div>
                                                <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-amber-700">
                                                    Perlu Dicek
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm leading-6 text-slate-600">
                                    Belum ada produk yang menyentuh batas minimum stok.
                                </p>
                            @endif
                        </div>
                    </div>
                </section>

            </article>
        </section>
    </div>
@endsection
