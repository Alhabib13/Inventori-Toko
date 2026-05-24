@extends('layouts.app')

@section('page_title', 'Edit User')
@section('page_subtitle', 'Perbarui data user internal, password, dan status aktif sesuai kebutuhan operasional.')

@section('content')
    <div class="mx-auto max-w-3xl">
        @if ($errors->any())
            <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h2 class="text-sm font-bold">Gagal Memperbarui User</h2>
                    <p class="mt-1 text-sm">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <label for="name" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                </div>

                <div class="space-y-2">
                    <label for="username" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Role User</label>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($allowedRoles as $roleValue => $roleLabel)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="role" value="{{ $roleValue }}" class="peer sr-only" @checked(old('role', $user->role) === $roleValue)>
                                <div class="rounded-xl border border-[#c0c8cb] bg-white p-4 transition peer-checked:border-[#003441] peer-checked:bg-[#d0e1fb]/25 hover:bg-[#f9f9fa]">
                                    <p class="font-semibold text-slate-900">{{ $roleLabel }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ $roleValue === 'kasir' ? 'Akses transaksi dan operasional kasir harian.' : 'Akses kontrol stok dan pembelian inventori.' }}
                                    </p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status User</label>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="is_active" value="1" class="peer sr-only" @checked((string) old('is_active', $user->is_active ? '1' : '0') === '1')>
                            <div class="rounded-xl border border-[#c0c8cb] bg-white p-4 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-[#f9f9fa]">
                                <p class="font-semibold text-slate-900">Aktif</p>
                                <p class="mt-1 text-sm text-slate-500">User bisa login dan memakai modul sesuai rolenya.</p>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="is_active" value="0" class="peer sr-only" @checked((string) old('is_active', $user->is_active ? '1' : '0') === '0')>
                            <div class="rounded-xl border border-[#c0c8cb] bg-white p-4 transition peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:bg-[#f9f9fa]">
                                <p class="font-semibold text-slate-900">Nonaktif</p>
                                <p class="mt-1 text-sm text-slate-500">User tidak bisa login sampai statusnya diaktifkan kembali.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="password" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Password Baru</label>
                        <input id="password" name="password" type="password" placeholder="Kosongkan jika tidak ingin mengganti" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                        <p class="text-xs text-slate-500">Password hanya diubah jika field ini diisi.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ketik ulang password baru" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('users.show', $user) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
