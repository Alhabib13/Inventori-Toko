@extends('layouts.app')

@php
    $canManageSuppliers = auth()->user()?->role === 'gudang' && auth()->user()?->mode_app === 'lengkap';
    $inactiveSupplierCount = $suppliers->count() - $activeSupplierCount;
@endphp

@section('page_title', 'Supplier')
@section('page_subtitle', 'Kelola daftar supplier aktif dan pantau informasi kontak untuk kebutuhan pembelian.')

@section('page_actions')
    @if ($canManageSuppliers)
        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('suppliers.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                Tambah Supplier
            </a>
            <form method="POST" action="{{ route('suppliers.destroy-all') }}" data-confirm="Semua supplier toko ini yang belum dipakai produk atau pembelian akan dihapus. Lanjutkan?" data-confirm-title="Hapus Semua Supplier">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                    Hapus Semua Supplier
                </button>
            </form>
        </div>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Supplier</p>
                <p class="mt-2 text-3xl font-bold text-[#003441]">{{ $suppliers->count() }}</p>
                <p class="mt-1 text-sm text-slate-500">Daftar supplier untuk operasional pembelian dan restock.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier Aktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $activeSupplierCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Bisa digunakan pada transaksi pembelian aktif.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier Nonaktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $inactiveSupplierCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Tetap tersimpan untuk arsip tetapi tidak diprioritaskan.</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Daftar Supplier</h2>
                <p class="mt-1 text-sm text-slate-500">Pantau kontak supplier dan status aktifnya untuk mendukung pembelian operasional.</p>
                @if (! $canManageSuppliers)
                    <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-600">
                        Mode Read Only
                    </p>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Supplier</th>
                            <th class="px-6 py-3">Kontak</th>
                            <th class="px-6 py-3">Telepon</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($suppliers as $supplier)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $supplier->nama_supplier }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($supplier->alamat, 48) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p class="font-medium text-slate-900">{{ $supplier->nama_kontak }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($supplier->keterangan ?: 'Belum ada catatan supplier.', 42) }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $supplier->telepon }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $supplier->email ?: '-' }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $supplier->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('suppliers.show', $supplier) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Detail
                                        </a>
                                        @if ($canManageSuppliers)
                                            <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex h-9 items-center rounded-lg border border-[#c0c8cb] px-3 text-sm font-medium text-[#003441] transition hover:bg-[#f3f4f5]">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="Hapus supplier ini dari daftar?" data-confirm-title="Hapus Supplier">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-9 items-center rounded-lg border border-red-100 px-3 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-0">
                                    <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada supplier yang tersimpan.</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Tambahkan supplier untuk mulai menyimpan data kontak pembelian, alamat, dan status operasionalnya.</p>
                                        @if ($canManageSuppliers)
                                            <a href="{{ route('suppliers.create') }}" class="mt-5 inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                                Tambah Supplier
                                            </a>
                                        @endif
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
