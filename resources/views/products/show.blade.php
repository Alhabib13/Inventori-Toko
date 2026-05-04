@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold">{{ $product->nama_produk }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $product->kode_produk }}</p>
            </div>

            @if ($canManageProducts)
                <a href="{{ route('products.edit', $product) }}" class="inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                    Edit Produk
                </a>
            @endif
        </div>

        <dl class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Kategori</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->kategori?->nama_kategori ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Supplier</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->supplier?->nama_supplier ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Harga Beli</dt>
                <dd class="mt-1 text-sm text-slate-900">Rp{{ number_format((float) $product->harga_beli, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Harga Jual</dt>
                <dd class="mt-1 text-sm text-slate-900">Rp{{ number_format((float) $product->harga_jual, 0, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Stok Minimum</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->stok_minimum }} {{ $product->satuan }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Status</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</dd>
            </div>
        </dl>

        @if ($product->deskripsi)
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-slate-900">Deskripsi</h4>
                <p class="mt-2 text-sm text-slate-600">{{ $product->deskripsi }}</p>
            </div>
        @endif
    </section>
@endsection
