@extends('layouts.app')

@section('page_title', 'Edit Produk')
@section('page_subtitle', $requiresSupplier
    ? 'Perbarui data produk, supplier, harga dasar, dan stok minimum untuk menjaga data gudang tetap akurat.'
    : 'Perbarui data utama produk inventori.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Ringkasan Produk</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kode Produk</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->kode_produk }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Saat Ini</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->stok }} {{ $product->satuan }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori Saat Ini</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->kategori?->nama_kategori ?? '-' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-lg font-semibold text-slate-900">Perbarui Data Produk</h2>
                <p class="mt-1 text-sm text-slate-500">Sesuaikan informasi kategori, supplier, harga, dan stok minimum agar data produk tetap akurat.</p>
            </div>
            <form method="POST" action="{{ route('products.update', $product) }}">
                @method('PUT')
                @include('products._form')
            </form>
        </section>
    </div>
@endsection
