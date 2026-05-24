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
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori Aktif</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $categories->count() }}</p>
                        <p class="mt-1 text-sm text-slate-500">Dipakai untuk klasifikasi dan filter produk.</p>
                    </div>
                    @if ($requiresSupplier)
                        <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier Aktif</p>
                            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $suppliers->count() }}</p>
                            <p class="mt-1 text-sm text-slate-500">Dipakai untuk relasi pembelian dan restock.</p>
                        </div>
                    @endif
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Informasi Dasar</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Isi nama produk, satuan, kategori, dan supplier agar data inventori mudah ditelusuri oleh tim gudang.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga & Batas Minimum</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Tentukan harga dasar dan stok minimum sejak awal untuk mempermudah pembelian ulang dan notifikasi restock.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Pengisian</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Jika kategori atau supplier belum tersedia, lengkapi data master terlebih dahulu agar produk dapat disimpan dengan benar.</p>
                </div>
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Alur Cepat</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <p>1. Tentukan kategori dan supplier yang sesuai.</p>
                        <p>2. Isi harga beli, harga jual, dan stok minimum.</p>
                        <p>3. Simpan produk lalu lanjutkan monitoring dari daftar produk.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-lg font-semibold text-slate-900">Form Produk Baru</h2>
                <p class="mt-1 text-sm text-slate-500">Lengkapi data inti produk untuk monitoring harga, stok, kategori, dan supplier.</p>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama & Satuan</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Identitas utama produk untuk daftar dan transaksi.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori & Supplier</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Menghubungkan produk ke data master inventori.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga & Stok</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Menentukan dasar monitoring margin dan batas minimum stok.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('products.store') }}">
                @include('products._form')
            </form>
        </section>
    </div>
@endsection
