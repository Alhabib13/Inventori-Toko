@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $activeCount = $categories->where('is_active', true)->count();
    $inactiveCount = $categories->where('is_active', false)->count();
@endphp

@section('page_title', 'Kategori')
@section('page_subtitle', $isSimpleMode
    ? 'Kelola daftar kategori produk dan proses tambah/edit/hapus kategori untuk owner mode sederhana.'
    : ($canManageCategories
        ? 'Kelola klasifikasi produk untuk membantu operasional gudang menjaga struktur inventori tetap rapi.'
        : 'Pantau klasifikasi produk untuk monitoring owner mode lengkap tanpa perubahan data operasional.'))

@section('page_actions')
    @if ($canManageCategories)
        <a href="{{ route('categories.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Tambah Kategori
        </a>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Kategori</p>
                <p class="mt-2 text-3xl font-bold text-[#003441]">{{ $categories->count() }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $canManageCategories ? 'Kelola klasifikasi produk dari satu tempat.' : 'Pantau struktur kategori untuk monitoring produk.' }}</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori Aktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $activeCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Kategori aktif dipakai pada produk yang sedang berjalan.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori Nonaktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $inactiveCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Memudahkan evaluasi kategori yang sudah tidak dipakai.</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Klasifikasi Produk</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Kategori membantu owner memisahkan produk agar pencatatan dan laporan tetap rapi.' : ($canManageCategories ? 'Gunakan kategori untuk mempermudah pencatatan barang, penataan stok, dan operasional gudang harian.' : 'Gunakan kategori untuk mempermudah analisis stok, produk, dan laporan bisnis.') }}</p>
                @if (! $canManageCategories)
                    <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-600">
                        Mode Read Only
                    </p>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Kategori</th>
                            <th class="px-6 py-3">Deskripsi</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($categories as $category)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $category->nama_kategori }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $category->slug }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <p class="line-clamp-2 max-w-xl">{{ $category->deskripsi ?: 'Belum ada deskripsi kategori.' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('categories.show', $category) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Detail
                                        </a>
                                        @if ($canManageCategories)
                                            <a href="{{ route('categories.edit', $category) }}" class="inline-flex h-9 items-center rounded-lg border border-[#c0c8cb] px-3 text-sm font-medium text-[#003441] transition hover:bg-[#f3f4f5]">
                                                Edit
                                            </a>
                                            <form action="{{ route('categories.destroy', $category) }}" method="POST">
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
                                <td colspan="4" class="px-6 py-0">
                                    <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada kategori yang tersimpan.</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Tambahkan kategori untuk mulai mengelompokkan produk dan membuat struktur inventori lebih rapi.</p>
                                        @if ($canManageCategories)
                                            <a href="{{ route('categories.create') }}" class="mt-5 inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                                Tambah Kategori
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
