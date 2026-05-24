@extends('layouts.app')

@section('page_title', 'Detail User')
@section('page_subtitle', 'Lihat data user internal dan status aksesnya di toko aktif.')

@section('page_actions')
    <a href="{{ route('users.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Kembali
    </a>
    @if ($user->role !== 'owner')
        <a href="{{ route('users.edit', $user) }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Edit User
        </a>
    @endif
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-[#d0e1fb] text-sm font-bold text-[#003441]">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">{{ $user->name }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ ucfirst($user->role) }} · {{ $user->username }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status Akun</p>
                <div class="mt-3">
                    <span class="inline-flex items-center gap-2 rounded-full {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                        <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Informasi User</h3>
            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Lengkap</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $user->name }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Username</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $user->username }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Role</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($user->role) }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Mode Toko</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($user->mode_app ?? '-') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Toko</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $user->store_name }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
