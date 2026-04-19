@extends('layouts.auth')

@php
    $judulHalaman = 'Daftar Owner';
    $authTab = 'DAFTAR';
@endphp

@section('auth_headline', 'Bangun Toko Anda.')

@section('auth_copy')
    Bangun workspace owner yang siap dipakai untuk memantau stok, produk, dan penjualan sejak hari pertama.
@endsection

@section('auth_features')
    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 7v10M7 12h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Onboarding owner yang simpel</p>
            <p class="mt-1 text-sm leading-6 text-slate-300">Daftarkan toko dan pemilik utama tanpa alur yang bertele-tele.</p>
        </div>
    </div>
    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M7 16.5V9.75A2.75 2.75 0 0 1 9.75 7h4.5A2.75 2.75 0 0 1 17 9.75v6.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                <path d="M5 16.5h14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Siap lanjut ke dashboard</p>
            <p class="mt-1 text-sm leading-6 text-slate-300">Desain halaman daftar dibuat satu bahasa visual dengan halaman login owner.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="mx-auto flex h-full w-full max-w-[30rem] flex-col justify-center">
        <div class="space-y-2">
            <h2 class="text-3xl font-bold tracking-[-0.04em] text-slate-900 sm:text-[2.15rem]">Buat akun owner Anda</h2>
            <p class="text-sm leading-7 text-slate-500 sm:text-base">Isi data dasar toko dan owner untuk memulai workspace Sitori.</p>
        </div>

        <form class="mt-8 space-y-5">
            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="store_name" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Nama Toko</label>
                    <input
                        id="store_name"
                        name="store_name"
                        type="text"
                        placeholder="contoh: Toko Sentosa"
                        class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                    />
                </div>

                <div class="space-y-2">
                    <label for="owner_name" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Nama Owner</label>
                    <input
                        id="owner_name"
                        name="owner_name"
                        type="text"
                        placeholder="nama pemilik toko"
                        class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                    />
                </div>
            </div>

            <div class="space-y-2">
                <label for="register_email" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Email Administrator</label>
                <input
                    id="register_email"
                    name="email"
                    type="email"
                    placeholder="owner@sitori.app"
                    class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="register_password" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Password</label>
                    <input
                        id="register_password"
                        name="password"
                        type="password"
                        placeholder="........"
                        class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                    />
                </div>

                <div class="space-y-2">
                    <label for="register_password_confirmation" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Konfirmasi Password</label>
                    <input
                        id="register_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="........"
                        class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                    />
                </div>
            </div>

            <button type="button" class="h-[3.25rem] w-full rounded-xl bg-[#12345c] px-4 text-base font-semibold text-white shadow-[0_16px_30px_rgba(18,52,92,0.22)] transition hover:bg-[#102f53] focus:outline-none focus:ring-4 focus:ring-[#163760]/15">
                Daftarkan Akun Owner
            </button>

            <div class="relative pt-6 text-center">
                <div class="absolute inset-x-0 top-1/2 border-t border-slate-200"></div>
                <span class="relative bg-white px-4 text-[0.68rem] font-bold uppercase tracking-[0.18em] text-slate-400">Sudah punya akun?</span>
            </div>

            <a href="{{ route('login') }}" class="flex h-[3.25rem] w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-[#163760] transition hover:border-[#163760]/30 hover:bg-slate-50">
                Masuk ke dashboard
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                    <path d="M4.167 10h11.666m0 0-4.166-4.167M15.833 10l-4.166 4.167" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </form>
    </div>
@endsection