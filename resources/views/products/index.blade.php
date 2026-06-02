@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $showImportButton = $isSimpleMode || (auth()->user()?->role === 'gudang' && auth()->user()?->mode_app === 'lengkap');
    $showProductTools = (auth()->user()?->role === 'owner' && $isSimpleMode)
        || (auth()->user()?->role === 'gudang' && auth()->user()?->mode_app === 'lengkap');
    $search = $search ?? '';
    $lastPage = $products->lastPage();
    $currentPage = $products->currentPage();
    $startPage = max(1, min($currentPage - 3, max(1, $lastPage - 6)));
    $endPage = min($lastPage, $startPage + 6);
    $totalProductCount = $productSummary['total'] ?? $products->total();
    $lowStockCount = $productSummary['low_stock'] ?? 0;
    $inactiveCount = $productSummary['inactive'] ?? 0;
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
            @if ($showProductTools)
                <a href="{{ route('products.template.download') }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                    Download Template CSV
                </a>
            @endif

            @if ($showImportButton)
                <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3" data-upload-form>
                    @csrf
                    <input type="file" name="import_file" accept=".csv,text/csv" class="sr-only" data-upload-input>
                    <button
                        type="button"
                        class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]"
                        data-upload-trigger
                        data-loading-text="Mengimpor produk..."
                    >
                        Import Produk & Stok Awal
                    </button>
                </form>
            @endif

            <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                Tambah Produk
            </a>

            @if ($showProductTools)
                <form method="POST" action="{{ route('products.destroy-all') }}" data-confirm="Semua produk yang belum dipakai transaksi atau pembelian akan dihapus. Lanjutkan?" data-confirm-title="Hapus Semua Produk">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex h-11 items-center rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                        Hapus Semua Produk
                    </button>
                </form>
            @endif
        </div>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        @error('import_file')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Produk</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalProductCount }}</p>
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
            <div class="flex flex-col gap-4 border-b border-[#c0c8cb] px-6 py-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-slate-900">Daftar Produk</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Tampilkan daftar produk, harga jual beli, stok per produk, dan aksi pengelolaan sederhana.' : ($canManageProducts ? 'Tampilkan harga, stok minimum, supplier terkait, dan status produk aktif.' : 'Tampilkan data produk aktif untuk evaluasi owner tanpa aksi perubahan data.') }}</p>
                    @if ($showImportButton && $showProductTools)
                    @endif
                    @if (! $canManageProducts)
                        <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-600">
                            Mode Read Only
                        </p>
                    @endif
                </div>
                <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[22rem]">
                    <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <label for="products-search" class="sr-only">Cari produk</label>
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </span>
                            <input
                                id="products-search"
                                type="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari nama, kode, kategori, atau supplier..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                Cari
                            </button>
                            @if ($search !== '')
                                <a href="{{ route('products.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
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
                                            <form method="POST" action="{{ route('products.destroy', $product) }}" data-confirm="Hapus produk ini dari daftar inventori?" data-confirm-title="Hapus Produk">
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
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm text-slate-500">
                        Menampilkan
                        <span class="font-semibold text-slate-700">{{ $products->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold text-slate-700">{{ $products->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-semibold text-slate-700">{{ $products->total() }}</span>
                        produk
                    </div>
                </div>

                @if ($lastPage > 1)
                    <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                            @if ($search !== '')
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif
                            <span>Lompat ke halaman</span>
                            <input
                                type="number"
                                name="page"
                                min="1"
                                max="{{ $lastPage }}"
                                value="{{ $currentPage }}"
                                class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]"
                            >
                            <span>dari {{ $lastPage }}</span>
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Buka
                            </button>
                        </form>

                        <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                            @if ($products->onFirstPage())
                                <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">
                                    Sebelumnya
                                </span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Sebelumnya
                                </a>
                            @endif

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                @if ($page === $currentPage)
                                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $products->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endfor

                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Berikutnya
                                </a>
                            @else
                                <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">
                                    Berikutnya
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
