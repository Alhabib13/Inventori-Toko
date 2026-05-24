@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Edit Kategori')
@section('page_subtitle', $isSimpleMode
    ? 'Perbarui nama atau deskripsi kategori agar daftar produk owner mode sederhana tetap konsisten.'
    : 'Perbarui klasifikasi kategori untuk mendukung pengelolaan produk dan stok yang lebih rapi.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Kategori</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Slug Saat Ini</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $category->slug }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status</p>
                    <span class="mt-2 inline-flex items-center gap-2 rounded-full {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                        <span class="h-2 w-2 rounded-full {{ $category->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Saat Ini</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $category->nama_kategori }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('categories.update', $category) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-2 border-b border-slate-200 pb-5">
                    <h2 class="text-lg font-semibold text-slate-900">Perbarui Data Kategori</h2>
                    <p class="text-sm leading-6 text-slate-500">Sesuaikan nama atau deskripsi kategori agar klasifikasi produk tetap jelas dan konsisten.</p>
                </div>
                @include('categories._form', ['category' => $category])

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('categories.show', $category) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Lihat Detail
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Update Kategori
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
