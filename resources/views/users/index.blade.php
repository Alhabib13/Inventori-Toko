@extends('layouts.app')

@section('page_title', 'Manajemen User')
@section('page_subtitle', $isSimpleMode
    ? 'Kelola user owner dan kasir yang aktif di mode toko sederhana.'
    : 'Pantau user owner, kasir, dan gudang sesuai mode toko lengkap.')

@section('page_actions')
    <a href="{{ route('users.register') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Tambah User
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.82fr_1.18fr]">
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
                        <span class="text-sm text-slate-600">{{ $isSimpleMode ? 'User Gudang Aktif' : 'Gudang' }}</span>
                        <span class="text-sm font-semibold text-slate-900">{{ $isSimpleMode ? 0 : $warehouseCount }}</span>
                    </div>
                </div>
                <div class="rounded-xl border border-[#cde2e8] bg-[#eff7f8] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Mode Toko Aktif</p>
                    <p class="mt-2 text-base font-semibold text-[#003441]">{{ $isSimpleMode ? 'Owner Sederhana' : 'Owner Lengkap' }}</p>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ $isSimpleMode ? 'Role yang bisa ditambahkan hanya kasir agar alur operasional tetap sederhana.' : 'Role kasir dan gudang dapat dikelola sesuai kebutuhan operasional toko lengkap.' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#c0c8cb] px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Daftar User</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Owner mode sederhana hanya mengelola role kasir.' : 'Owner mode lengkap dapat memantau user kasir dan gudang.' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-[#cde2e8] bg-[#eff7f8] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0f4c5c]">
                        {{ $users->count() }} User
                    </span>
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600">
                        {{ $isSimpleMode ? 'Role tersedia: Kasir' : 'Role tersedia: Kasir & Gudang' }}
                    </span>
                </div>
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
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-900">{{ ucfirst($listedUser->role) }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $listedUser->role === 'kasir' ? 'Fokus transaksi penjualan harian.' : ($listedUser->role === 'gudang' ? 'Fokus stok, pembelian, dan operasional gudang.' : 'Akses pengawasan toko secara menyeluruh.') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $listedUser->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $listedUser->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $listedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                    <p class="mt-2 text-xs text-slate-500">
                                        {{ $listedUser->is_active ? 'User dapat login dan memakai akses sesuai role.' : 'User tersimpan tetapi sementara tidak bisa login.' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('users.show', $listedUser) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Detail
                                        </a>
                                        @if ($listedUser->role !== 'owner')
                                            <a href="{{ route('users.edit', $listedUser) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                                Edit
                                            </a>
                                            <form action="{{ route('users.toggle-status', $listedUser) }}" method="POST" data-confirm="{{ $listedUser->is_active ? 'Nonaktifkan user ini agar tidak bisa login sementara?' : 'Aktifkan kembali user ini agar bisa login?' }}" data-confirm-title="{{ $listedUser->is_active ? 'Nonaktifkan User' : 'Aktifkan User' }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex h-9 items-center rounded-lg border {{ $listedUser->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' }} px-3 text-sm font-medium transition">
                                                    {{ $listedUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8">
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada user internal yang terdaftar.</p>
                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $isSimpleMode ? 'Tambahkan user kasir pertama untuk membantu operasional toko sederhana.' : 'Tambahkan user kasir atau gudang untuk mulai membagi operasional toko lengkap.' }}
                                        </p>
                                        <div class="mt-5">
                                            <a href="{{ route('users.register') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                                Tambah User
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
