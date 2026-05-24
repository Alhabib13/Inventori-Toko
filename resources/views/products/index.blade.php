@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $showImportButton = $isSimpleMode || (auth()->user()?->role === 'gudang' && auth()->user()?->mode_app === 'lengkap');
    $lowStockCount = $products->getCollection()->filter(fn ($product) => $product->stok <= $product->stok_minimum)->count();
    $inactiveCount = $products->getCollection()->where('is_active', false)->count();
@endphp

@section('page_title', 'Produk')
@section('page_subtitle', $isSimpleMode
    ? 'Kelola daftar produk, tambah/edit/hapus produk, harga beli dan jual, serta stok per produk untuk owner mode sederhana.'
    : ($canManageProducts
        ? 'Kelola daftar produk, detail stok, harga jual dan beli, serta supplier terkait untuk operasional gudang.'
        : 'Pantau daftar produk, harga, stok, dan supplier terkait untuk monitoring owner mode lengkap.'))

@section('page_actions')
    @if ($canManageProducts)
        <div class="flex flex-wrap items-center justify-end gap-3">
            @if ($showImportButton)
                <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <label class="inline-flex h-11 cursor-pointer items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        <input type="file" name="import_file" accept=".csv,text/csv" class="sr-only" onchange="this.form.submit()">
                        Import Produk & Stok Awal
                    </label>
                </form>
            @endif

            <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                Tambah Produk
            </a>
        </div>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @error('import_file')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Produk</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $products->total() }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $canManageProducts ? 'Data produk aktif untuk pengelolaan harian.' : 'Data produk untuk monitoring owner.' }}</p>
            </article>
            <article class="rounded-2xl border {{ $lowStockCount > 0 ? 'border-amber-200 bg-amber-50/70' : 'border-[#c0c8cb] bg-white' }} p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-slate-500' }}">Stok Perlu Dicek</p>
                <p class="mt-2 text-3xl font-bold {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-slate-900' }}">{{ $lowStockCount }}</p>
                <p class="mt-1 text-sm {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-slate-500' }}">Produk yang stoknya sudah menyentuh batas minimum.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Nonaktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $inactiveCount }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ $canManageProducts ? 'Produk nonaktif tidak ikut transaksi aktif.' : 'Lihat status produk aktif dan nonaktif dari satu halaman.' }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#c0c8cb] px-6 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Daftar Produk</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Tampilkan daftar produk, harga jual beli, stok per produk, dan aksi pengelolaan sederhana.' : ($canManageProducts ? 'Tampilkan harga, stok minimum, supplier terkait, dan status produk aktif.' : 'Tampilkan data produk aktif untuk evaluasi owner tanpa aksi perubahan data.') }}</p>
                    @if ($showImportButton)
                        <p class="mt-2 text-xs text-slate-500">
                            Format CSV:
                            <span class="font-mono">
                                {{ $requiresSupplier
                                    ? 'nama_produk,kategori,supplier,satuan,harga_beli,harga_jual,stok_awal,stok_minimum'
                                    : 'nama_produk,kategori,satuan,harga_beli,harga_jual,stok_awal,stok_minimum' }}
                            </span>.
                            File Excel simpan dulu sebagai CSV.
                        </p>
                    @endif
                    @if (! $canManageProducts)
                        <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-600">
                            Mode Read Only
                        </p>
                    @endif
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
                                    @if ($product->stok <= $product->stok_minimum)
                                        <span class="mt-2 inline-flex rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-amber-700">
                                            Stok Menipis
                                        </span>
                                    @endif
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
                                <td colspan="{{ $isSimpleMode ? '6' : '7' }}" class="px-6 py-0">
                                    <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada produk yang tersimpan.</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Tambahkan produk pertama untuk mulai memantau harga, stok, kategori, dan supplier dari satu halaman.</p>
                                        @if ($canManageProducts)
                                            <a href="{{ route('products.create') }}" class="mt-5 inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                                Tambah Produk
                                            </a>
                                        @endif
                                    </div>
                                </td>
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
