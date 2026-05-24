@extends('layouts.app')

@section('page_title', 'Detail Produk')
@section('page_subtitle', $requiresSupplier
    ? 'Tinjau detail barang, satuan, harga dasar, supplier, dan status stok untuk operasional gudang.'
    : 'Tinjau detail utama produk inventori toko.')

@section('page_actions')
    @if ($canManageProducts)
        <a href="{{ route('products.edit', $product) }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Edit Produk
        </a>
    @endif
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">{{ $product->nama_produk }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $product->kode_produk }}</p>

            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Saat Ini</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $product->stok }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $product->satuan }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status Produk</p>
                    <span class="mt-2 inline-flex items-center gap-2 rounded-full {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                        <span class="h-2 w-2 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="rounded-xl border {{ $product->stok <= $product->stok_minimum ? 'border-amber-200 bg-amber-50/70' : 'border-emerald-200 bg-emerald-50/70' }} p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] {{ $product->stok <= $product->stok_minimum ? 'text-amber-700' : 'text-emerald-700' }}">Kondisi Stok</p>
                    <p class="mt-2 text-sm font-semibold {{ $product->stok <= $product->stok_minimum ? 'text-amber-700' : 'text-emerald-700' }}">
                        {{ $product->stok <= $product->stok_minimum ? 'Perlu perhatian karena stok sudah menyentuh batas minimum.' : 'Stok masih aman untuk operasional saat ini.' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Informasi Produk</h2>
                <p class="mt-1 text-sm text-slate-500">Rincian kategori, supplier, harga dasar, dan pengaturan stok minimum.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Ringkasan Produk</p>
                    <p class="mt-2 text-base font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $product->kode_produk }} - {{ $product->satuan }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->kategori?->nama_kategori ?? '-' }}</p>
                </div>
                @if ($requiresSupplier)
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->supplier?->nama_supplier ?? '-' }}</p>
                    </div>
                @endif
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Beli</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $product->harga_beli, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Jual</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $product->harga_jual, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Potensi Margin</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format((float) ($product->harga_jual - $product->harga_beli), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Minimum</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->stok_minimum }} {{ $product->satuan }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Satuan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $product->satuan }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $product->deskripsi ?: 'Belum ada deskripsi produk.' }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
