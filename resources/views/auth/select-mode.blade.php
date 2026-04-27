@extends('layouts.auth')

@php
    $judulHalaman = 'Pilih Mode Toko';
    $authTab = 'MODE TOKO';
@endphp

@section('auth_headline', 'Mulai Dengan Mode Toko.')

@section('auth_copy')
    Sebelum masuk dashboard, pilih mode operasional yang paling sesuai dengan kebutuhan toko Anda saat ini.
@endsection

@section('auth_features')
    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 12h10M7 8h10M7 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Mode Sederhana</p>
            <p class="mt-1 text-sm leading-6 text-slate-300">Cocok untuk toko kecil dengan alur operasional yang cepat dan struktur tim yang ringkas.</p>
        </div>
    </div>
    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 16.5V9.75A2.75 2.75 0 0 1 8.75 7h6.5A2.75 2.75 0 0 1 18 9.75v6.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M4.5 16.5h15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Mode Lengkap</p>
            <p class="mt-1 text-sm leading-6 text-slate-300">Cocok untuk toko dengan kebutuhan stok dan operasional yang lebih lengkap.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="mx-auto flex h-full w-full max-w-[34rem] flex-col justify-center">
        <div class="space-y-2">
            <h2 class="text-3xl font-bold tracking-[-0.04em] text-slate-900 sm:text-[2.15rem]">Pilih mode toko Anda</h2>
            <p class="text-sm leading-7 text-slate-500 sm:text-base">Owner wajib memilih mode toko terlebih dahulu sebelum mengakses dashboard dan fitur utama aplikasi.</p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('mode-selection.store') }}" method="POST" class="mt-8 space-y-5">
            @csrf

            <label class="block cursor-pointer rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-[#163760]/35 has-[:checked]:border-[#163760] has-[:checked]:bg-[#f4f7fb]">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-slate-900">Mode Sederhana</p>
                        <p class="text-sm leading-6 text-slate-500">Untuk toko kecil dengan fokus owner dan kasir agar alur operasional lebih ringkas.</p>
                    </div>
                    <input type="radio" name="mode_app" value="sederhana" class="mt-1 h-5 w-5 shrink-0 accent-[#163760]" @checked(old('mode_app') === 'sederhana')>
                </div>
            </label>

            <label class="block cursor-pointer rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-[#163760]/35 has-[:checked]:border-[#163760] has-[:checked]:bg-[#f4f7fb]">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <p class="text-lg font-semibold text-slate-900">Mode Lengkap</p>
                        <p class="text-sm leading-6 text-slate-500">Untuk toko dengan pengelolaan stok lebih detail dan struktur operasional yang lebih besar.</p>
                    </div>
                    <input type="radio" name="mode_app" value="lengkap" class="mt-1 h-5 w-5 shrink-0 accent-[#163760]" @checked(old('mode_app') === 'lengkap')>
                </div>
            </label>

            <button type="submit" class="h-[3.25rem] w-full rounded-xl bg-[#12345c] px-4 text-base font-semibold text-white shadow-[0_16px_30px_rgba(18,52,92,0.22)] transition hover:bg-[#102f53] focus:outline-none focus:ring-4 focus:ring-[#163760]/15">
                Simpan dan Lanjutkan
            </button>
        </form>
    </div>
@endsection
