@extends('layouts.auth')

@php
    $judulHalaman = 'Lupa Kata Sandi';
@endphp

@section('auth_heading', 'Reset sandi khusus untuk akun owner')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-6 text-center shadow-sm sm:px-6">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-[#0b4a5a]/10 text-[#0b4a5a] shadow-inner">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 10V7a4 4 0 0 0-8 0v3" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2" />
            </svg>
        </div>

        <div class="mt-5">
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-800">Lupa Kata Sandi</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Gunakan halaman ini hanya untuk memulai proses reset sandi akun owner.
            </p>
        </div>

        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-left text-amber-900">
            <div class="flex gap-3">
                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-bold">Peringatan akses reset sandi</p>
                    <p class="mt-1 text-sm leading-6">
                        Lupa kata sandi hanya berlaku untuk owner. Jika pegawai lupa kata sandi, silakan hubungi owner.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 space-y-3 text-center">
            <a
                href="{{ route('password.owner-reset') }}"
                class="inline-flex h-11 w-full items-center justify-center rounded-md bg-[#083d4b] px-4 text-sm font-bold text-white shadow-sm transition hover:bg-[#062f39]"
            >
                Lanjutkan Reset Sandi Owner
            </a>

            <a href="{{ route('login') }}" class="inline-flex text-sm font-bold text-[#0b4a5a] hover:underline">
                Kembali ke halaman masuk
            </a>
        </div>
    </div>
@endsection
