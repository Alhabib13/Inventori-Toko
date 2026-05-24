@extends('layouts.app')

@section('page_title', 'Tambah Produk')
@section('page_subtitle', $requiresSupplier
    ? 'Tambahkan produk baru beserta kategori, supplier, harga dasar, dan stok minimum untuk operasional gudang.'
    : 'Lengkapi data utama produk untuk kebutuhan inventori toko.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Panduan Tambah Produk</h2>
            <div class="mt-5 space-y-4">
                @if ($categories->isEmpty() || ($requiresSupplier && $suppliers->isEmpty()))
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-700">
                        {{ $requiresSupplier
                            ? 'Produk membutuhkan kategori dan supplier aktif. Tambahkan dulu data master kategori dan supplier.'
                            : 'Produk membutuhkan kategori aktif. Tambahkan dulu data master kategori.' }}
                    </div>
                @endif
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Informasi Dasar</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Isi nama produk, satuan, kategori, dan supplier agar data inventori mudah ditelusuri oleh tim gudang.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga & Batas Minimum</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Tentukan harga dasar dan stok minimum sejak awal untuk mempermudah pembelian ulang dan notifikasi restock.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('products.store') }}">
                @include('products._form')
            </form>
        </section>
    </div>
@endsection
