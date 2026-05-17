@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Detail Prediksi Stok')
@section('page_subtitle', $isSimpleMode
    ? 'Lihat detail prediksi untuk membantu owner membaca kebutuhan stok sederhana dan tren penjualan.'
    : 'Lihat detail prediksi stok untuk mengevaluasi hasil analisis dan kebutuhan restock produk.')

@section('page_actions')
    <a href="{{ route('forecasts.edit', $forecast) }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#f3f4f5]">
        Edit Prediksi
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Analisis</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Prediksi Stok</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $forecast->prediksi_stok ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Aktual</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ $forecast->stok_aktual ?? '-' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ $forecast->produk?->nama_produk ?? 'Produk tidak tersedia' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $forecast->catatan ?: 'Belum ada catatan analisis untuk prediksi ini.' }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
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
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $forecast->nilai_moving_average ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Panjang Jendela</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $forecast->panjang_jendela ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Selisih Prediksi</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $forecast->selisih_prediksi ?? '-' }}</p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                <a href="{{ route('forecasts.index') }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                    Kembali ke Prediksi Stok
                </a>
            </div>
        </section>
    </div>
@endsection
