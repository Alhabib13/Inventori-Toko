@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Detail Prediksi Stok')
@section('page_subtitle', $isSimpleMode
    ? 'Lihat detail prediksi untuk membantu owner membaca kebutuhan stok sederhana dan tren penjualan.'
    : 'Lihat detail prediksi stok untuk mengevaluasi hasil analisis dan kebutuhan restock produk.')

@section('page_actions')
    @if ($canManageForecasts)
        <div class="flex items-center gap-3">
            <a href="{{ route('forecasts.edit', $forecast) }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#f3f4f5]">
                Edit Prediksi
            </a>
            <form action="{{ route('forecasts.destroy', $forecast) }}" method="POST" onsubmit="return confirm('Hapus prediksi stok ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-red-200 bg-white px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    Hapus
                </button>
            </form>
        </div>
    @endif
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Analisis</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Prediksi Stok</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $forecast->prediksi_stok ?? '-' }} {{ $forecast->produk?->satuan ?? '' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Aktual</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ $forecast->stok_aktual ?? '-' }} {{ $forecast->produk?->satuan ?? '' }}</p>
                </div>
                <div class="rounded-xl border {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'border-red-200 bg-red-50/70' : 'border-emerald-200 bg-emerald-50/70' }} p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'text-red-600' : 'text-emerald-700' }}">Rekomendasi Restock</p>
                            <p class="mt-2 text-sm leading-6 {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'text-red-700' : 'text-emerald-800' }}">
                                {{ ($forecast->selisih_prediksi ?? 0) > 0
                                    ? 'Forecast menunjukkan kebutuhan tambahan stok untuk periode berikutnya.'
                                    : 'Stok saat ini masih mencukupi untuk forecast periode berikutnya.' }}
                            </p>
                        </div>
                        <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ ($forecast->selisih_prediksi ?? 0) > 0 ? 'Restock ' . ($forecast->selisih_prediksi ?? 0) : 'Aman' }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ $forecast->produk?->nama_produk ?? 'Produk tidak tersedia' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $forecast->catatan ?: 'Belum ada catatan analisis untuk prediksi ini.' }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Forecast</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $forecast->produk?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $forecast->produk?->kode_produk ?? '-' }} @if($forecast->produk?->satuan) - {{ $forecast->produk?->satuan }} @endif</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Awal</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($forecast->periode_awal)->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Akhir</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ optional($forecast->periode_akhir)->translatedFormat('d M Y') ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Moving Average</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $forecast->nilai_moving_average, 2, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Panjang Jendela</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $forecast->panjang_jendela ?? '-' }} bulan</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Rekomendasi Restock</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">
                        {{ ($forecast->selisih_prediksi ?? 0) > 0
                            ? ($forecast->selisih_prediksi ?? 0) . ' ' . ($forecast->produk?->satuan ?? '') . ' perlu disiapkan untuk restock'
                            : 'Tidak ada kebutuhan restock tambahan untuk forecast ini' }}
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Histori Penjualan per Periode</h3>
                        <p class="mt-1 text-sm text-slate-500">Data ini menjadi dasar perhitungan moving average dan rekomendasi restock.</p>
                    </div>
                    <span class="rounded-full bg-[#d0e1fb]/40 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0f4c5c]">
                        {{ count($series) }} Periode
                    </span>
                </div>

                @if (count($series) > 0)
                    <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                                    <tr>
                                        <th class="px-5 py-3">Periode</th>
                                        <th class="px-5 py-3">Jumlah Terjual</th>
                                        <th class="px-5 py-3">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200">
                                    @foreach ($series as $point)
                                        <tr>
                                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $point['label'] }}</td>
                                            <td class="px-5 py-4 text-slate-700">{{ $point['qty'] }} {{ $forecast->produk?->satuan ?? '' }}</td>
                                            <td class="px-5 py-4 text-slate-500">
                                                {{ $point['qty'] > 0 ? 'Masuk perhitungan moving average' : 'Tidak ada transaksi pada periode ini' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-8 text-center">
                        <p class="text-base font-semibold text-slate-900">Belum ada histori penjualan.</p>
                        <p class="mt-2 text-sm text-slate-500">Forecast ini belum memiliki data per periode yang cukup untuk ditampilkan.</p>
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                <a href="{{ route('forecasts.index') }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                    Kembali ke Prediksi Stok
                </a>
            </div>
        </section>
    </div>
@endsection
