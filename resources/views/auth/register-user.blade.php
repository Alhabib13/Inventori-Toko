@extends('layouts.auth')

@php
    $judulHalaman = 'Daftarkan Pengguna Baru';
@endphp

@section('content')
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-slate-900">Daftarkan Pengguna</h2>
        <p class="text-sm text-slate-500 mt-2">Buat akun operasional untuk tim toko Anda</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
            <div class="w-5 h-5 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-red-800">Gagal Mendaftarkan</h3>
                <p class="text-xs text-red-600 mt-1 leading-relaxed">
                    {{ $errors->first() }}
                </p>
            </div>
        </div>
    @endif

    <form action="{{ route('users.register.process') }}" method="POST" class="space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="name" class="text-sm font-semibold text-slate-700 ml-1">Nama Pengguna (Tampilan)</label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#00303F] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    class="block w-full h-11 pl-11 pr-4 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-700 transition-all outline-none focus:bg-white focus:border-[#00303F] focus:ring-4 focus:ring-[#00303F]/5 placeholder:text-slate-400"
                />
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label for="register_username" class="text-sm font-semibold text-slate-700 ml-1">Username (Login)</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#00303F] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        class="block w-full h-11 pl-11 pr-4 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-700 transition-all outline-none focus:bg-white focus:border-[#00303F] focus:ring-4 focus:ring-[#00303F]/5 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="role" class="text-sm font-semibold text-slate-700 ml-1">Role / Jabatan</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#00303F] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <select
                        id="role"
                        name="role"
                        class="block w-full h-11 pl-11 pr-4 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-700 transition-all outline-none focus:bg-white focus:border-[#00303F] focus:ring-4 focus:ring-[#00303F]/5 appearance-none cursor-pointer"
                    >
                        @foreach ($allowedRoles as $roleValue => $roleLabel)
                            <option value="{{ $roleValue }}" @selected(old('role') === $roleValue)>{{ $roleLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="space-y-1.5">
                <label for="register_password" class="text-sm font-semibold text-slate-700 ml-1">Kata Sandi</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#00303F] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="register_password"
                        name="password"
                        type="password"
                        placeholder="••••••••••••"
                        required
                        class="block w-full h-11 pl-11 pr-4 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-700 transition-all outline-none focus:bg-white focus:border-[#00303F] focus:ring-4 focus:ring-[#00303F]/5 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="register_password_confirmation" class="text-sm font-semibold text-slate-700 ml-1">Konfirmasi</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#00303F] transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="register_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="••••••••••••"
                        required
                        class="block w-full h-11 pl-11 pr-4 rounded-xl border border-slate-200 bg-slate-50/50 text-sm font-medium text-slate-700 transition-all outline-none focus:bg-white focus:border-[#00303F] focus:ring-4 focus:ring-[#00303F]/5 placeholder:text-slate-400"
                    />
                </div>
            </div>
        </div>

        <button type="submit" class="w-full h-12 bg-[#00303F] text-white rounded-xl font-bold text-sm shadow-lg shadow-[#00303F]/20 hover:bg-[#002530] active:scale-[0.98] transition-all duration-200 mt-2">
            Daftarkan Pengguna
        </button>

        <div class="pt-4 text-center">
            <a href="{{ route('users.index') }}" class="text-xs font-bold text-slate-500 hover:text-[#00303F] transition-colors">
                Kembali ke Daftar Pengguna
            </a>
        </div>
    </form>
@endsection
