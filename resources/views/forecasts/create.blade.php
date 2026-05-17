@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Tambah Prediksi Stok')
@section('page_subtitle', $isSimpleMode
    ? 'Susun prediksi stok sederhana untuk membantu owner menentukan rekomendasi restock harian.'
    : 'Susun prediksi stok baru untuk memantau kebutuhan restock dan tren penjualan produk.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Panduan Prediksi</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Analisis</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Pilih periode yang mewakili pola penjualan produk agar hasil prediksi lebih relevan.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Rekomendasi Restock</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan kolom catatan untuk menulis kebutuhan restock atau catatan tren produk.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form action="{{ route('forecasts.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('forecasts._form', ['forecast' => null])

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('forecasts.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Prediksi
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
