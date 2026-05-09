@extends('layouts.auth')

@php
    $judulHalaman = 'Pilih Mode Toko';
@endphp

@section('content')
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Pilih Mode Toko</h2>
        <p class="text-sm text-slate-500 mt-2">Sesuaikan fitur aplikasi dengan kebutuhan operasional toko Anda</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
            <div class="w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-red-800">Pilihan Diperlukan</h3>
                <p class="text-xs text-red-600 mt-1 leading-relaxed">
                    {{ $errors->first() }}
                </p>
            </div>
        </div>
    @endif

    <form action="{{ route('mode-selection.store') }}" method="POST" class="space-y-4">
        @csrf

        <label class="relative block cursor-pointer group">
            <input type="radio" name="mode_app" value="sederhana" class="peer absolute opacity-0" @checked(old('mode_app') === 'sederhana')>
            <div class="p-5 rounded-2xl border-2 border-slate-100 bg-white transition-all duration-200 peer-checked:border-[#00303F] peer-checked:bg-[#00303F]/5 group-hover:border-slate-200">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0 group-hover:bg-white transition-colors peer-checked:bg-white">
                        <svg class="w-5 h-5 text-[#00303F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Mode Sederhana</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Untuk toko kecil dengan fokus owner dan kasir agar alur operasional lebih ringkas.</p>
                    </div>
                </div>
                <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                    <div class="w-5 h-5 rounded-full bg-[#00303F] flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </label>

        <label class="relative block cursor-pointer group">
            <input type="radio" name="mode_app" value="lengkap" class="peer absolute opacity-0" @checked(old('mode_app') === 'lengkap')>
            <div class="p-5 rounded-2xl border-2 border-slate-100 bg-white transition-all duration-200 peer-checked:border-[#00303F] peer-checked:bg-[#00303F]/5 group-hover:border-slate-200">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center shrink-0 group-hover:bg-white transition-colors peer-checked:bg-white">
                        <svg class="w-5 h-5 text-[#00303F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">Mode Lengkap</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Untuk toko dengan pengelolaan stok lebih detail dan struktur operasional yang lebih besar.</p>
                    </div>
                </div>
                <div class="absolute top-4 right-4 opacity-0 peer-checked:opacity-100 transition-opacity">
                    <div class="w-5 h-5 rounded-full bg-[#00303F] flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>
        </label>

        <button type="submit" class="w-full h-12 bg-[#00303F] text-white rounded-xl font-bold text-sm shadow-lg shadow-[#00303F]/20 hover:bg-[#002530] active:scale-[0.98] transition-all duration-200 mt-4">
            Simpan dan Lanjutkan
        </button>
    </form>
@endsection
