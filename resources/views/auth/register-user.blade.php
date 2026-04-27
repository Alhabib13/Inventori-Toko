@extends('layouts.auth')

@php
    $judulHalaman = 'Daftar Pengguna';
    $authTab = 'PENGGUNA';
@endphp

@section('auth_headline', 'Kelola Tim Toko.')

@section('auth_copy')
    Owner dapat membuat akun operasional sesuai mode toko yang dipilih.
@endsection

@section('auth_features')
    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Akun dibuat oleh owner</p>
            <p class="mt-1 text-sm leading-6 text-slate-300">Halaman ini hanya bisa diakses owner yang sudah login.</p>
        </div>
    </div>
    <div class="flex items-start gap-4 rounded-2xl border border-white/10 bg-white/10 px-4 py-4 backdrop-blur-sm">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 12.75 10 16l8-9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold">Role operasional</p>
            <p class="mt-1 text-sm leading-6 text-slate-300">Opsi role mengikuti mode toko aktif.</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="mx-auto flex h-full w-full max-w-[30rem] flex-col justify-center">
        <div class="space-y-2">
            <h2 class="text-3xl font-bold tracking-[-0.04em] text-slate-900 sm:text-[2.15rem]">Daftarkan pengguna toko</h2>
            <p class="text-sm leading-7 text-slate-500 sm:text-base">Buat akun operasional dengan role yang tersedia untuk mode toko saat ini.</p>
        </div>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('users.register.process') }}" method="POST" class="mt-8 space-y-5">
            @csrf

            <div class="space-y-2">
                <label for="name" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Nama Pengguna</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    placeholder="nama pengguna"
                    class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="register_username" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Username</label>
                    <input
                        id="register_username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        placeholder="contoh: kasir01"
                        class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                    />
                </div>

                <div class="space-y-2">
                    <label for="role" class="block text-[0.72rem] font-bold uppercase tracking-[0.18em] text-slate-400">Role</label>
                    <select
                        id="role"
                        name="role"
                        class="h-[3.25rem] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-[#163760] focus:bg-white focus:ring-4 focus:ring-[#163760]/10"
                    >
                        @foreach ($allowedRoles as $roleValue => $roleLabel)
                            <option value="{{ $roleValue }}" @selected(old('role') === $roleValue)>{{ $roleLabel }}</option>
                        @endforeach
                    </select>
                </div>
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

            <button type="submit" class="h-[3.25rem] w-full rounded-xl bg-[#12345c] px-4 text-base font-semibold text-white shadow-[0_16px_30px_rgba(18,52,92,0.22)] transition hover:bg-[#102f53] focus:outline-none focus:ring-4 focus:ring-[#163760]/15">
                Daftarkan Pengguna
            </button>

            <a href="{{ route('users.index') }}" class="flex h-[3.25rem] w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-[#163760] transition hover:border-[#163760]/30 hover:bg-slate-50">
                Kembali ke pengguna
            </a>
        </form>
    </div>
@endsection
