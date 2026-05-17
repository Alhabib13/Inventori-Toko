@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
@endphp

@section('page_title', 'Produk')
@section('page_subtitle', $isSimpleMode
    ? 'Kelola daftar produk, tambah/edit/hapus produk, harga beli dan jual, serta stok per produk untuk owner mode sederhana.'
    : 'Kelola daftar produk, detail stok, harga jual dan beli, serta supplier terkait untuk owner mode lengkap.')

@section('page_actions')
    @if ($canManageProducts)
        <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Tambah Produk
        </a>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#c0c8cb] px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Daftar Produk</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Tampilkan daftar produk, harga jual beli, stok per produk, dan aksi pengelolaan sederhana.' : 'Tampilkan harga, stok minimum, supplier terkait, dan status produk aktif.' }}</p>
                </div>
                <span class="rounded-full bg-[#d0e1fb]/35 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0f4c5c]">
                    {{ $products->total() }} Produk
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-[980px] w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            @unless($isSimpleMode)
                                <th class="px-6 py-3">Supplier</th>
                            @endunless
                            <th class="px-6 py-3">Harga</th>
                            <th class="px-6 py-3">{{ $isSimpleMode ? 'Stok per Produk' : 'Detail Stok' }}</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($products as $product)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                    <p class="mt-1 font-mono text-xs text-slate-500">{{ $product->kode_produk }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                                @unless($isSimpleMode)
                                    <td class="px-6 py-4 text-slate-600">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                                @endunless
                                <td class="px-6 py-4 text-slate-600">
                                    <div>Beli: <span class="font-semibold text-slate-900">Rp{{ number_format((float) $product->harga_beli, 0, ',', '.') }}</span></div>
                                    <div class="mt-1">Jual: <span class="font-semibold text-slate-900">Rp{{ number_format((float) $product->harga_jual, 0, ',', '.') }}</span></div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    <div>Stok: <span class="font-semibold text-slate-900">{{ $product->stok }} {{ $product->satuan }}</span></div>
                                    <div class="mt-1">Min: {{ $product->stok_minimum }} {{ $product->satuan }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('products.show', $product) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Detail
                                        </a>
                                        @if ($canManageProducts)
                                            <a href="{{ route('products.edit', $product) }}" class="inline-flex h-9 items-center rounded-lg border border-[#c0c8cb] px-3 text-sm font-medium text-[#003441] transition hover:bg-[#f3f4f5]">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('products.destroy', $product) }}">
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
                                <td colspan="{{ $isSimpleMode ? '6' : '7' }}" class="px-6 py-8 text-center text-slate-500">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-6 py-4">
                {{ $products->links() }}
            </div>
        </section>
    </div>
@endsection
