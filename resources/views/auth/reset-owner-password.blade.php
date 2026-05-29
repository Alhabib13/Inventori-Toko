@extends('layouts.auth')

@php
    $judulHalaman = 'Reset Sandi Owner';
@endphp

@section('auth_heading', 'Verifikasi owner sebelum mengganti sandi')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-6 shadow-sm sm:px-6">
        <div class="text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-[#0b4a5a]/10 text-[#0b4a5a] shadow-inner">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 10V7a4 4 0 0 0-8 0v3" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 10h12a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14.5v2.5" />
                </svg>
            </div>

            <h2 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-800">Reset Sandi Owner</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">
                Masukkan email owner, kode verifikasi, dan sandi baru untuk melanjutkan proses reset.
            </p>
        </div>

        <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-900">
            Pastikan email yang dimasukkan adalah email akun owner. Pegawai tetap perlu menghubungi owner jika lupa kata sandi.
        </div>

        <form action="#" method="POST" class="mt-6 space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="owner_email" class="block text-sm font-bold text-slate-700">Email Owner</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                        </svg>
                    </div>
                    <input
                        id="owner_email"
                        name="owner_email"
                        type="email"
                        placeholder="owner@email.com"
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="verification_code" class="block text-sm font-bold text-slate-700">Kode Verifikasi</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M8 4h8l2 3v13H6V7l2-3Z" />
                        </svg>
                    </div>
                    <input
                        id="verification_code"
                        name="verification_code"
                        type="text"
                        inputmode="numeric"
                        placeholder="Masukkan kode verifikasi"
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="new_password" class="block text-sm font-bold text-slate-700">Sandi Baru</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4h8Z" />
                            </svg>
                        </div>
                        <input
                            id="new_password"
                            name="new_password"
                            type="password"
                            placeholder="Sandi baru"
                            class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-10 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                        />
                        <button type="button" data-password-toggle="new_password" aria-label="Tampilkan sandi baru" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-[#0b4a5a]">
                            <svg data-eye-open class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
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

                <div class="space-y-1.5">
                    <label for="new_password_confirmation" class="block text-sm font-bold text-slate-700">Konfirmasi Sandi</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3 5 6v5c0 4.5 2.9 8.7 7 10 4.1-1.3 7-5.5 7-10V6l-7-3Z" />
                            </svg>
                        </div>
                        <input
                            id="new_password_confirmation"
                            name="new_password_confirmation"
                            type="password"
                            placeholder="Ulangi sandi baru"
                            class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-10 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                        />
                        <button type="button" data-password-toggle="new_password_confirmation" aria-label="Tampilkan konfirmasi sandi" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-[#0b4a5a]">
                            <svg data-eye-open class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
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
            </div>

            <button type="button" class="h-11 w-full rounded-md bg-[#083d4b] text-sm font-bold text-white transition hover:bg-[#062f39]">
                Reset Sandi Owner
            </button>

            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-sm font-bold text-[#0b4a5a] hover:underline">
                    Kembali ke info lupa kata sandi
                </a>
            </div>
        </form>
    </div>
@endsection
