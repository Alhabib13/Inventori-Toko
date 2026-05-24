@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $productCount = $category->produk()->count();
@endphp

@section('page_title', 'Detail Kategori')
@section('page_subtitle', $isSimpleMode
    ? 'Lihat ringkasan kategori untuk memastikan pengelompokan produk owner mode sederhana tetap jelas.'
    : 'Lihat detail kategori untuk membantu klasifikasi produk pada mode lengkap tetap terkontrol.')

@section('page_actions')
    @if ($canManageCategories)
        <a href="{{ route('categories.edit', $category) }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-[#003441] transition hover:bg-[#f3f4f5]">
            Edit Kategori
        </a>
    @endif
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Statistik Kategori</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jumlah Produk</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $productCount }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status</p>
                    <span class="mt-2 inline-flex items-center gap-2 rounded-full {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                        <span class="h-2 w-2 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="rounded-xl border {{ $category->is_active ? 'border-emerald-200 bg-emerald-50/70' : 'border-slate-200 bg-slate-100/80' }} p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] {{ $category->is_active ? 'text-emerald-700' : 'text-slate-600' }}">Kondisi Kategori</p>
                    <p class="mt-2 text-sm font-semibold {{ $category->is_active ? 'text-emerald-700' : 'text-slate-600' }}">
                        {{ $category->is_active ? 'Kategori aktif dan siap dipakai pada data produk.' : 'Kategori nonaktif dan tidak diprioritaskan untuk operasional saat ini.' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ $category->nama_kategori }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $category->deskripsi ?: 'Belum ada deskripsi kategori.' }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Ringkasan Kategori</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $category->nama_kategori }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $category->slug }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Kategori</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $category->nama_kategori }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Slug</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $category->slug }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $category->deskripsi ?: 'Belum ada deskripsi kategori untuk data ini.' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Penggunaan di Produk</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $productCount > 0
                            ? 'Kategori ini sudah dipakai oleh ' . $productCount . ' produk dan perlu dijaga konsistensinya.'
                            : 'Kategori ini belum dipakai oleh produk mana pun.' }}
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                <a href="{{ route('categories.index') }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                    Kembali ke Daftar Kategori
                </a>
            </div>
        </section>
    </div>
@endsection
