@extends('layouts.app')

@php
    $forecastRows = \App\Models\SalesForecast::query()->with('produk')->latest()->take(8)->get();
    $trendingProducts = \App\Models\Product::query()->orderByDesc('stok')->take(5)->get();
    $transactionCount = \App\Models\Transaction::query()->count();
@endphp

@section('page_title', 'Prediksi Stok')
@section('page_subtitle', 'Prediksi stok per produk, kebutuhan restock, dan analisis tren penjualan untuk owner mode lengkap.')

@section('page_actions')
    <a href="{{ route('forecasts.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Tambah Prediksi
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Analisis Tren Penjualan</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Prediksi stok di halaman ini digunakan untuk membantu owner menilai kebutuhan restock berdasarkan histori transaksi dan laju pergerakan produk.
                </p>
                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Data Prediksi</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $forecastRows->count() }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Terpantau</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $trendingProducts->count() }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Riwayat Penjualan</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($transactionCount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Kebutuhan Restock</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($trendingProducts as $product)
                        @php
                            $restockGap = max(($product->stok_minimum ?? 0) - ($product->stok ?? 0), 0);
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $product->kode_produk }}</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ $restockGap > 0 ? 'bg-red-50 text-red-600' : 'bg-[#d0e1fb]/40 text-[#0f4c5c]' }}">
                                    {{ $restockGap > 0 ? 'Perlu Restock' : 'Aman' }}
                                </span>
                            </div>
                            <p class="mt-3 text-sm text-slate-600">Stok saat ini {{ $product->stok }} {{ $product->satuan }}. Batas minimum {{ $product->stok_minimum }} {{ $product->satuan }}.</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada produk untuk dianalisis.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Prediksi Stok per Produk</h2>
                <p class="mt-1 text-sm text-slate-500">Tabel prediksi, nilai moving average, dan selisih dengan stok aktual.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Periode</th>
                            <th class="px-6 py-3">Prediksi</th>
                            <th class="px-6 py-3">Stok Aktual</th>
                            <th class="px-6 py-3">Selisih</th>
                            <th class="px-6 py-3">Moving Avg</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($forecastRows as $forecast)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $forecast->produk?->nama_produk ?? 'Produk tidak ditemukan' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $forecast->produk?->kode_produk ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ optional($forecast->periode_awal)->format('d M Y') ?? '-' }} - {{ optional($forecast->periode_akhir)->format('d M Y') ?? '-' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $forecast->prediksi_stok ?? 0 }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $forecast->stok_aktual ?? 0 }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'bg-red-50 text-red-600' : 'bg-[#d0e1fb]/40 text-[#0f4c5c]' }}">
                                        {{ $forecast->selisih_prediksi ?? 0 }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ number_format((float) $forecast->nilai_moving_average, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada data prediksi stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
