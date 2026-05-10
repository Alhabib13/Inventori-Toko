@extends('layouts.auth')

@php
    $judulHalaman = 'Pilih Mode Toko';
@endphp

@section('auth_container_class', 'w-full max-w-4xl')

@section('content')
    <div class="mb-8 text-center">
        <h2 class="text-[2rem] leading-tight font-extrabold tracking-tight text-slate-800">Pilih Mode Operasional Toko</h2>
        <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-slate-500">
            Sesuaikan pengalaman Sitori dengan skala dan kebutuhan manajemen inventaris bisnis Anda saat ini.
            Anda dapat mengubahnya nanti.
        </p>
    </div>

    @if ($errors->any())
        <div class="mx-auto mb-6 flex max-w-2xl items-start gap-3 rounded-md border border-red-300 bg-red-50 px-4 py-3">
            <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-red-700">Pilihan Diperlukan</h3>
                <p class="mt-1 text-xs leading-relaxed text-red-600">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <form action="{{ route('mode-selection.store') }}" method="POST" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <label class="group relative block cursor-pointer">
                <input type="radio" name="mode_app" value="sederhana" class="peer sr-only" @checked(old('mode_app', 'sederhana') === 'sederhana')>
                <div class="pointer-events-none absolute right-4 top-4 z-10 flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-transparent transition peer-checked:border-[#0b4a5a] peer-checked:bg-[#0b4a5a] peer-checked:text-white">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="h-full rounded-2xl border border-slate-300 bg-white p-5 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:border-[#0b4a5a]/50 group-hover:shadow-md peer-checked:-translate-y-1 peer-checked:border-2 peer-checked:border-[#0b4a5a] peer-checked:bg-[#0b4a5a]/[0.03] peer-checked:shadow-[0_14px_34px_rgba(11,74,90,0.14)]">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-[#123b4a]">Mode Sederhana</h3>
                            <p class="mt-2 pr-10 text-sm leading-relaxed text-slate-500">
                                Cocok untuk toko kecil dengan manajemen stok dasar. Tampilan ringkas untuk transaksi cepat dan pencatatan sederhana.
                            </p>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-4">
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-[#0b4a5a]"></span>
                                <span>Pencatatan barang masuk/keluar dasar</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-[#0b4a5a]"></span>
                                <span>Peringatan stok menipis</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-[#0b4a5a]"></span>
                                <span>Dashboard kasir yang dipercepat</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </label>

            <label class="group relative block cursor-pointer">
                <input type="radio" name="mode_app" value="lengkap" class="peer sr-only" @checked(old('mode_app') === 'lengkap')>
                <div class="pointer-events-none absolute right-4 top-4 z-10 flex h-6 w-6 items-center justify-center rounded-full border border-slate-300 bg-white text-transparent transition peer-checked:border-[#0b4a5a] peer-checked:bg-[#0b4a5a] peer-checked:text-white">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="h-full rounded-2xl border border-slate-300 bg-white p-5 transition-all duration-200 group-hover:-translate-y-0.5 group-hover:border-[#0b4a5a]/50 group-hover:shadow-md peer-checked:-translate-y-1 peer-checked:border-2 peer-checked:border-[#0b4a5a] peer-checked:bg-[#0b4a5a]/[0.03] peer-checked:shadow-[0_14px_34px_rgba(11,74,90,0.14)]">
                    <div class="mb-4 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Mode Lengkap</h3>
                            <p class="mt-2 pr-10 text-sm leading-relaxed text-slate-500">
                                Ideal untuk bisnis skala besar dengan fitur gudang, manajemen supplier, dan laporan analitik mendalam.
                            </p>
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-4">
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></span>
                                <span>Manajemen multi-gudang</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></span>
                                <span>Pelacakan batch dan kedaluwarsa</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-slate-400"></span>
                                <span>Laporan audit stok dan profitabilitas</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </label>
        </div>

        <div class="border-t border-slate-200 pt-6 text-center">
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-md bg-[#083d4b] px-6 text-sm font-bold text-white transition hover:bg-[#062f39]">
                Lanjutkan dengan Mode Terpilih
            </button>
        </div>
    </form>
@endsection
