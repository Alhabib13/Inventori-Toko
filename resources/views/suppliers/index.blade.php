@extends('layouts.app')

@section('page_title', 'Supplier')
@section('page_subtitle', 'Kelola daftar supplier aktif dan pantau informasi kontak untuk kebutuhan pembelian.')

@section('page_actions')
    <a href="{{ route('suppliers.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Tambah Supplier
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.85fr_1.15fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Supplier</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Supplier</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $suppliers->count() }}</p>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Supplier Aktif</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $activeSupplierCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3">
                    <span class="text-sm text-slate-600">Supplier Nonaktif</span>
                    <span class="text-sm font-semibold text-slate-900">{{ $suppliers->count() - $activeSupplierCount }}</span>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Daftar Supplier</h2>
                <p class="mt-1 text-sm text-slate-500">Pantau kontak supplier dan status aktifnya untuk mendukung pembelian operasional.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Supplier</th>
                            <th class="px-6 py-3">Kontak</th>
                            <th class="px-6 py-3">Telepon</th>
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
                                <td class="px-6 py-4 text-slate-600">{{ $supplier->nama_kontak }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $supplier->telepon }}</td>
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
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">Belum ada data supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
