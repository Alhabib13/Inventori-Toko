@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold">Daftar Produk</h3>
                <p class="mt-1 text-sm text-slate-600">Data produk, harga, supplier, dan batas stok minimum.</p>
            </div>

            @if ($canManageProducts)
                <a href="{{ route('products.create') }}" class="inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                    Tambah Produk
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Produk</th>
                        <th class="py-3 pr-4">Kategori</th>
                        <th class="py-3 pr-4">Supplier</th>
                        <th class="py-3 pr-4">Harga</th>
                        <th class="py-3 pr-4">Stok Min.</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="py-3 pr-4">
                                <div class="font-medium text-slate-900">{{ $product->nama_produk }}</div>
                                <div class="text-xs text-slate-500">{{ $product->kode_produk }}</div>
                            </td>
                            <td class="py-3 pr-4">{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                            <td class="py-3 pr-4">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                            <td class="py-3 pr-4">
                                <div>Beli: Rp{{ number_format((float) $product->harga_beli, 0, ',', '.') }}</div>
                                <div>Jual: Rp{{ number_format((float) $product->harga_jual, 0, ',', '.') }}</div>
                            </td>
                            <td class="py-3 pr-4">{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                            <td class="py-3 pr-4">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="py-3 pr-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-slate-700">Detail</a>
                                    @if ($canManageProducts)
                                        <a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-blue-700">Edit</a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-semibold text-red-700">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </section>
@endsection
