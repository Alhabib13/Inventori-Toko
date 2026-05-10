@extends('layouts.auth')

@php
    $judulHalaman = 'Daftarkan Pengguna Baru';
@endphp

@section('content')
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-800">Daftarkan pengguna</h2>
        <p class="mt-2 text-sm text-slate-500">Buat akun operasional untuk tim toko Anda</p>
    </div>

    @if ($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-md border border-red-300 bg-red-50 px-4 py-3">
            <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-red-700">Gagal Mendaftarkan</h3>
                <p class="mt-1 text-xs leading-relaxed text-red-600">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <div class="rounded-xl border border-slate-200 bg-white px-4 py-5 shadow-sm sm:px-5 sm:py-6">
        <form action="{{ route('users.register.process') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-bold text-slate-700">Nama Pengguna</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama pengguna"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="register_username" class="block text-sm font-bold text-slate-700">Username</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input
                            id="register_username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            placeholder="contoh: kasir01"
                            required
                            class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                        />
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="role" class="block text-sm font-bold text-slate-700">Role / Jabatan</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <select
                            id="role"
                            name="role"
                            class="block h-11 w-full appearance-none rounded-md border border-slate-300 bg-white pl-9 pr-10 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10"
                        >
                            @foreach ($allowedRoles as $roleValue => $roleLabel)
                                <option value="{{ $roleValue }}" @selected(old('role') === $roleValue)>{{ $roleLabel }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <label for="register_password" class="block text-sm font-bold text-slate-700">Kata Sandi</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="register_password"
                            name="password"
                            type="password"
                            placeholder="Buat kata sandi"
                            required
                            class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                        />
                        <button
                            type="button"
                            data-password-toggle="register_password"
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

                <div class="space-y-1.5">
                    <label for="register_password_confirmation" class="block text-sm font-bold text-slate-700">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            id="register_password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Ketik ulang kata sandi"
                            required
                            class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                        />
                        <button
                            type="button"
                            data-password-toggle="register_password_confirmation"
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
            </div>

            <button type="submit" class="mt-1 h-11 w-full rounded-md bg-[#083d4b] text-sm font-bold text-white transition hover:bg-[#062f39]">
                Daftarkan Pengguna
            </button>

            <div class="border-t border-slate-100 pt-5 text-center">
                <a href="{{ route('users.index') }}" class="text-xs font-bold text-slate-500 transition hover:text-[#0b4a5a]">
                    Kembali ke Daftar Pengguna
                </a>
            </div>
        </form>
    </div>
@endsection
