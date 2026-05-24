@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Edit Prediksi Stok')
@section('page_subtitle', $isSimpleMode
    ? 'Perbarui prediksi stok sederhana agar rekomendasi owner tetap akurat untuk operasional harian.'
    : 'Perbarui parameter prediksi stok untuk menyesuaikan tren penjualan dan kebutuhan restock.')

@section('page_actions')
    <a href="{{ route('forecasts.show', $forecast) }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#f3f4f5]">
        Lihat Detail
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Prediksi</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Terkait</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $forecast->produk?->nama_produk ?? 'Produk tidak tersedia' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Selisih Prediksi</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $forecast->selisih_prediksi ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Moving Average Aktif</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ number_format((float) $forecast->nilai_moving_average, 2, ',', '.') }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form action="{{ route('forecasts.update', $forecast) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-2 border-b border-slate-200 pb-5">
                    <h2 class="text-lg font-semibold text-slate-900">Perbarui Parameter Forecast</h2>
                    <p class="text-sm leading-6 text-slate-500">Sesuaikan produk, periode akhir, atau jendela moving average agar hasil prediksi tetap relevan.</p>
                </div>

                @include('forecasts._form', ['forecast' => $forecast])

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('forecasts.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Kembali
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Update Prediksi
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
