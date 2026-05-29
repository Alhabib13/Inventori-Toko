@extends('layouts.auth')

@php
    $judulHalaman = 'Registrasi Pemilik Toko';
@endphp

@section('auth_heading', 'Registrasi Pemilik Toko')

@section('content')
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-extrabold tracking-tight text-slate-800">Buat akun pemilik</h2>
        <p class="mt-2 text-sm text-slate-500">Mulai kelola inventori toko Anda lebih profesional</p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white px-4 py-5 shadow-sm sm:px-5 sm:py-6">
        <form action="{{ route('register.process') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="store_name" class="block text-sm font-bold text-slate-700">Nama Toko</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <input
                        id="store_name"
                        name="store_name"
                        type="text"
                        value="{{ old('store_name') }}"
                        placeholder="Masukkan nama toko Anda"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="alamat_toko" class="block text-sm font-bold text-slate-700">Alamat Toko</label>
                <div class="relative">
                    <div class="pointer-events-none absolute left-0 top-0 flex items-center pl-3 pt-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s-6-5.33-6-11a6 6 0 1 1 12 0c0 5.67-6 11-6 11Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                        </svg>
                    </div>
                    <textarea
                        id="alamat_toko"
                        name="alamat_toko"
                        rows="3"
                        placeholder="Masukkan alamat toko Anda"
                        required
                        class="block w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    >{{ old('alamat_toko') }}</textarea>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-bold text-slate-700">Nama Lengkap</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </div>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama lengkap Anda"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

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
                        placeholder="Pilih nama pengguna"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-bold text-slate-700">Email Owner</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                        </svg>
                    </div>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="owner@email.com"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="verification_code" class="block text-sm font-bold text-slate-700">Kode Verifikasi</label>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6M8 4h8l2 3v13H6V7l2-3Z" />
                            </svg>
                        </div>
                        <input
                            id="verification_code"
                            name="verification_code"
                            type="text"
                            inputmode="numeric"
                            maxlength="6"
                            value="{{ old('verification_code') }}"
                            placeholder="Masukkan kode"
                            required
                            class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                        />
                    </div>
                    <button
                        type="submit"
                        formaction="{{ route('register.owner.send-code') }}"
                        formmethod="POST"
                        formnovalidate
                        data-loading-text="Mengirim..."
                        data-code-send-button
                        data-cooldown-seconds="120"
                        class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-md border border-[#0b4a5a] px-4 text-sm font-bold text-[#0b4a5a] transition hover:bg-[#0b4a5a]/5"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z" />
                        </svg>
                        <span data-code-send-label>Kirim Kode</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500">Kirim kode ke email owner dulu, lalu masukkan 6 digit verifikasi untuk menyelesaikan registrasi.</p>
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
                        placeholder="Buat kata sandi"
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

            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-sm font-bold text-slate-700">Konfirmasi Kata Sandi</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Ketik ulang kata sandi"
                        required
                        class="block h-11 w-full rounded-md border border-slate-300 bg-white pl-9 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#0b4a5a] focus:ring-2 focus:ring-[#0b4a5a]/10 placeholder:text-slate-400"
                    />
                    <button
                        type="button"
                        data-password-toggle="password_confirmation"
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

            <button type="submit" data-loading-text="Mendaftarkan..." class="mt-1 h-11 w-full rounded-md bg-[#083d4b] text-sm font-bold text-white transition hover:bg-[#062f39]">
                Daftar sebagai Pemilik
            </button>

            <div class="border-t border-slate-100 pt-5 text-center">
                <p class="text-xs font-medium text-slate-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="ml-1 font-bold text-[#0b4a5a] hover:underline">Masuk</a>
                </p>
            </div>
        </form>
    </div>

    <script>
        (() => {
            const sendButton = document.querySelector('[data-code-send-button]');
            if (!sendButton) return;

            const label = sendButton.querySelector('[data-code-send-label]');
            const cooldownSeconds = Number(sendButton.dataset.cooldownSeconds || 120);
            const storageKey = 'owner-register-code-cooldown-until';
            let timerId = null;

            const render = () => {
                const cooldownUntil = Number(sessionStorage.getItem(storageKey) || 0);
                const remaining = Math.max(0, Math.ceil((cooldownUntil - Date.now()) / 1000));

                if (remaining <= 0) {
                    sendButton.disabled = false;
                    if (label) label.textContent = 'Kirim Kode';
                    sessionStorage.removeItem(storageKey);
                    if (timerId) {
                        clearInterval(timerId);
                        timerId = null;
                    }
                    return;
                }

                sendButton.disabled = true;
                if (label) label.textContent = `Kirim Lagi (${remaining}s)`;
            };

            const startCooldown = () => {
                sessionStorage.setItem(storageKey, String(Date.now() + cooldownSeconds * 1000));
                render();
                if (!timerId) {
                    timerId = window.setInterval(render, 1000);
                }
            };

            sendButton.addEventListener('click', () => {
                const emailInput = document.getElementById('email');
                if (!(emailInput instanceof HTMLInputElement) || emailInput.value.trim() === '') {
                    return;
                }

                startCooldown();
            });

            render();
            if (sessionStorage.getItem(storageKey)) {
                timerId = window.setInterval(render, 1000);
            }
        })();
    </script>
@endsection
