@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Tambah Kategori')
@section('page_subtitle', $isSimpleMode
    ? 'Tambahkan kategori produk baru agar pencatatan owner mode sederhana tetap rapi.'
    : 'Tambahkan kategori baru untuk menjaga klasifikasi produk tetap terstruktur.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Panduan Tambah Kategori</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Tips Penamaan</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan nama kategori yang singkat, jelas, dan mudah dikenali seluruh tim operasional.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Slug Otomatis</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Slug akan dibuat otomatis dari nama kategori saat data disimpan.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('categories._form')

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('categories.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
