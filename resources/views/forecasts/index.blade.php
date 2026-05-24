@extends('layouts.app')

@section('page_title', 'Prediksi Stok')
@section('page_subtitle', $isSimpleMode
    ? 'Prediksi kebutuhan stok sederhana, rekomendasi restock, dan tren penjualan barang.'
    : ($canManageForecasts
        ? 'Prediksi kebutuhan barang gudang, rekomendasi restock, dan analisis pergerakan stok per produk.'
        : 'Prediksi stok per produk, kebutuhan restock, dan analisis tren penjualan untuk owner mode lengkap.'))

@section('page_actions')
    @if ($canManageForecasts)
        <a href="{{ route('forecasts.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Tambah Prediksi
        </a>
    @endif
@endsection

@section('content')
    @php
        $restockUrgentCount = $restockProducts->where('restock_gap', '>', 0)->count();
    @endphp

    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Prediksi Kebutuhan Stok Sederhana' : ($canManageForecasts ? 'Prediksi Kebutuhan Barang Gudang' : 'Analisis Tren Penjualan') }}</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    {{ $isSimpleMode
                        ? 'Halaman ini membantu owner sederhana memperkirakan kebutuhan stok tanpa analisis yang terlalu kompleks.'
                        : ($canManageForecasts
                            ? 'Halaman ini membantu tim gudang membaca kebutuhan restock berdasarkan histori pergerakan barang, prediksi stok, dan laju penjualan produk.'
                            : 'Prediksi stok di halaman ini digunakan untuk membantu owner menilai kebutuhan restock berdasarkan histori transaksi dan laju pergerakan produk.') }}
                </p>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Data Prediksi</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $forecastRows->total() }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Terpantau</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $restockProducts->count() }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Riwayat Penjualan</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($transactionCount, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border {{ $restockUrgentCount > 0 ? 'border-red-200 bg-red-50/70' : 'border-slate-200 bg-[#f9f9fa]' }} p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] {{ $restockUrgentCount > 0 ? 'text-red-600' : 'text-slate-500' }}">Perlu Restock</p>
                        <p class="mt-2 text-3xl font-bold {{ $restockUrgentCount > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $restockUrgentCount }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Rekomendasi Restock' : ($canManageForecasts ? 'Rekomendasi Restock Gudang' : 'Kebutuhan Restock') }}</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($restockProducts as $restockItem)
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $restockItem['product']->nama_produk }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $restockItem['product']->kode_produk }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ $restockItem['restock_gap'] > 0 ? 'bg-red-50 text-red-600' : 'bg-[#d0e1fb]/40 text-[#0f4c5c]' }}">
                                    {{ $restockItem['restock_gap'] > 0 ? 'Perlu Restock' : 'Aman' }}
                                </span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-lg bg-white/80 px-3 py-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Prediksi</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ $restockItem['forecast_qty'] }} {{ $restockItem['product']->satuan }}</p>
                                </div>
                                <div class="rounded-lg bg-white/80 px-3 py-2">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Stok Saat Ini</p>
                                    <p class="mt-1 font-semibold text-slate-900">{{ $restockItem['product']->stok }} {{ $restockItem['product']->satuan }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 bg-[#f9f9fa] px-5 py-8 text-center">
                            <p class="text-sm font-semibold text-slate-700">Belum ada rekomendasi restock.</p>
                            <p class="mt-2 text-sm text-slate-500">Forecast akan muncul di sini setelah produk memiliki histori transaksi yang cukup untuk dianalisis.</p>
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Tren Penjualan Barang' : ($canManageForecasts ? 'Prediksi Kebutuhan Barang per Produk' : 'Prediksi Stok per Produk') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Tabel prediksi sederhana untuk membantu owner membaca barang yang perlu diprioritaskan.' : ($canManageForecasts ? 'Tabel forecast ini membantu gudang membaca moving average, stok aktual, dan kebutuhan restock per produk.' : 'Tabel forecast ini membantu owner membaca nilai prediksi, stok aktual, dan kebutuhan restock tiap produk.') }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Periode</th>
                            <th class="px-6 py-3">Moving Avg</th>
                            <th class="px-6 py-3">Prediksi Stok</th>
                            <th class="px-6 py-3">Stok Aktual</th>
                            <th class="px-6 py-3">Rekomendasi Restock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($forecastRows as $forecast)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <a href="{{ route('forecasts.show', $forecast) }}" class="font-semibold text-slate-900 hover:text-[#0f4c5c]">{{ $forecast->produk?->nama_produk ?? 'Produk tidak ditemukan' }}</a>
                                    <p class="mt-1 text-xs text-slate-500">{{ $forecast->produk?->kode_produk ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ optional($forecast->periode_awal)->format('d M Y') ?? '-' }} - {{ optional($forecast->periode_akhir)->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ number_format((float) $forecast->nilai_moving_average, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $forecast->prediksi_stok ?? 0 }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $forecast->stok_aktual ?? 0 }}</td>
                                <td class="px-6 py-4">
                                    <div class="space-y-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'bg-red-50 text-red-600' : 'bg-[#d0e1fb]/40 text-[#0f4c5c]' }}">
                                            {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'Restock ' . ($forecast->selisih_prediksi ?? 0) : 'Stok Aman' }}
                                        </span>
                                        <p class="text-xs text-slate-500">
                                            {{ ($forecast->selisih_prediksi ?? 0) > 0
                                                ? 'Tambahkan stok untuk menjaga kebutuhan periode berikutnya.'
                                                : 'Stok saat ini masih cukup untuk kebutuhan forecast.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-0">
                                    <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada forecast yang tersimpan.</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Generate prediksi stok untuk mulai melihat moving average, rekomendasi restock, dan histori forecast per produk.</p>
                                        @if ($canManageForecasts)
                                            <a href="{{ route('forecasts.create') }}" class="mt-5 inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                                Generate Prediksi
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $forecastRows->links() }}
            </div>
        </section>
    </div>
@endsection
