@extends('layouts.auth')

@php
    $judulHalaman = 'Masuk';
@endphp

@section('auth_heading', 'Portal untuk Pemilik, Kasir, dan Admin Gudang')

@section('content')
    <div class="mb-6 text-center">
        <h2 class="text-[2rem] leading-tight font-extrabold tracking-tight text-slate-800">Selamat datang kembali</h2>
    </div>

    @if ($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-md border border-red-300 bg-red-50 px-4 py-3">
            <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-red-700">Autentikasi Gagal</h3>
                <p class="mt-1 text-xs leading-relaxed text-red-600">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white px-4 py-5 shadow-sm sm:px-5 sm:py-6">
        <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="username" class="block text-sm font-bold text-slate-700">Nama Pengguna</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        placeholder="Username"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-bold text-slate-700">Kata Sandi</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Masukkan kata sandi"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                    <button
                        type="button"
                        data-password-toggle="password"
                        aria-label="Tampilkan kata sandi"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-[#0b4a5a]"
                    >
                        <svg data-eye-open class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z" />
                        </svg>
                        <svg data-eye-closed class="hidden h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m3 3 18 18" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.584 10.587A2 2 0 0 0 12 14a2 2 0 0 0 1.414-.586" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.878 5.099A9.97 9.97 0 0 1 12 5c4.478 0 8.268 2.943 9.542 7a10.028 10.028 0 0 1-4.132 5.145M6.228 6.228A10.023 10.023 0 0 0 2.458 12c1.274 4.057 5.065 7 9.542 7a9.97 9.97 0 0 0 5.1-1.401" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-4 pt-1">
                <label class="flex cursor-pointer items-center gap-2 text-xs font-medium text-slate-500">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#0b4a5a] focus:ring-[#0b4a5a]" />
                    <span>Ingat saya</span>
                </label>
                <a href="#" class="text-xs font-bold text-[#0b4a5a] hover:underline">Lupa kata sandi?</a>
            </div>

            <button type="submit" class="mt-1 h-11 w-full rounded-md bg-[#083d4b] text-sm font-bold text-white transition hover:bg-[#062f39]">
                Masuk
            </button>

            <div class="border-t border-slate-100 pt-5 text-center">
                <p class="text-xs font-medium text-slate-500">
                    Pengguna baru?
                    <a href="{{ route('register') }}" class="ml-1 font-bold text-[#0b4a5a] hover:underline">Daftar sebagai Pemilik</a>
                </p>
            </div>
        </form>
    </div>
@endsection
