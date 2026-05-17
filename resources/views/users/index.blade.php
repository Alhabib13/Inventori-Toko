@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $users = \App\Models\User::query()->orderBy('role')->orderBy('name')->get();
    $ownerCount = $users->where('role', 'owner')->count();
    $cashierCount = $users->where('role', 'kasir')->count();
    $warehouseCount = $users->where('role', 'gudang')->count();
@endphp

@section('page_title', 'Manajemen User')
@section('page_subtitle', $isSimpleMode
    ? 'Kelola daftar user, lihat role user, serta aksi edit dan hapus untuk owner mode sederhana.'
    : 'Kelola daftar user owner, kasir, dan gudang, termasuk role user, status user, dan aksi edit/hapus.')

@section('page_actions')
    <a href="{{ route('users.register') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Tambah User
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Tim Operasional</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total User</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $users->count() }}</p>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                        <span class="text-sm text-slate-600">Owner</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $ownerCount }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                        <span class="text-sm text-slate-600">Kasir</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $cashierCount }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                        <span class="text-sm text-slate-600">{{ $isSimpleMode ? 'Kasir Aktif' : 'Gudang' }}</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $isSimpleMode ? $cashierCount : $warehouseCount }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#c0c8cb] px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Daftar User</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Mode sederhana fokus pada owner dan kasir agar pengelolaan user lebih ringkas.' : 'Status akun ditampilkan untuk memastikan user operasional siap digunakan.' }}</p>
                </div>
                <span class="rounded-full bg-[#d0e1fb]/35 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0f4c5c]">
                    {{ $users->count() }} User
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Nama</th>
                            <th class="px-6 py-3">Role User</th>
                            <th class="px-6 py-3">Status User</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($users as $listedUser)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#d0e1fb] text-xs font-bold text-[#003441]">
                                            {{ strtoupper(substr($listedUser->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ $listedUser->name }}</p>
                                            <p class="mt-1 text-xs text-slate-500">{{ $listedUser->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ ucfirst($listedUser->role) }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $listedUser->role === 'owner' ? 'bg-[#d0e1fb]/45 text-[#0f4c5c]' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $listedUser->role === 'owner' ? 'bg-[#0f4c5c]' : 'bg-emerald-500' }}"></span>
                                        Aktif
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('users.edit', $listedUser) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Edit
                                        </a>
                                        <form action="{{ route('users.destroy', $listedUser) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 items-center rounded-lg border border-red-100 px-3 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada data user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
