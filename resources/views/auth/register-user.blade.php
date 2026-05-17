@extends('layouts.app')

@section('page_title', 'Register User')
@section('page_subtitle', auth()->user()?->mode_app === 'sederhana'
    ? 'Form tambah user kasir untuk mendukung operasional owner mode sederhana.'
    : 'Form tambah user kasir atau gudang untuk mendukung operasional owner mode lengkap.')

@section('content')
    <div class="mx-auto max-w-3xl">
        @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h2 class="text-sm font-bold">Gagal Mendaftarkan User</h2>
                    <p class="mt-1 text-sm">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form action="{{ route('users.register.process') }}" method="POST" class="space-y-6">
                @csrf

                <div class="space-y-2">
                    <label for="name" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Masukkan nama lengkap user" required class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                </div>

                <div class="space-y-2">
                    <label for="register_username" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Username</label>
                    <input id="register_username" name="username" type="text" value="{{ old('username') }}" placeholder="contoh: kasir01" required class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Peran Akses</label>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($allowedRoles as $roleValue => $roleLabel)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="role" value="{{ $roleValue }}" class="peer sr-only" @checked(old('role', array_key_first($allowedRoles)) === $roleValue)>
                                <div class="rounded-xl border border-[#c0c8cb] bg-white p-4 transition peer-checked:border-[#003441] peer-checked:bg-[#d0e1fb]/25 hover:bg-[#f9f9fa]">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-9 w-9 items-center justify-center rounded-lg bg-[#d0e1fb]/45 text-[#003441]">
                                            @if ($roleValue === 'kasir')
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6.75h12a1 1 0 0 1 1 1v8.5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8.5a1 1 0 0 1 1-1Zm2.25 7.5h3.5m4.25 0h.01M8.75 10.25h6.5" />
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.75 7.75 7.25-3 7.25 3M4.75 7.75 12 11l7.25-3M4.75 7.75v8.5L12 19.5l7.25-3.25v-8.5M12 11v8.5" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900">{{ $roleLabel }}</p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $roleValue === 'kasir' ? 'Akses transaksi dan operasional kasir harian.' : 'Akses kontrol stok dan pergerakan barang.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="pointer-events-none absolute right-4 top-4 hidden text-[#003441] peer-checked:block">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="register_password" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Password Sementara</label>
                        <div class="relative">
                            <input id="register_password" name="password" type="password" placeholder="Buat password untuk user" required class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                            <button type="button" data-password-toggle="register_password" aria-label="Tampilkan kata sandi" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-[#003441]">
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

                    <div class="space-y-2">
                        <label for="register_password_confirmation" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Konfirmasi Password</label>
                        <div class="relative">
                            <input id="register_password_confirmation" name="password_confirmation" type="password" placeholder="Ketik ulang password" required class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                            <button type="button" data-password-toggle="register_password_confirmation" aria-label="Tampilkan kata sandi" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 transition hover:text-[#003441]">
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

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('users.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan User
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
