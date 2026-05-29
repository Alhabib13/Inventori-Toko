@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $isCashier = auth()->user()?->role === 'kasir';
    $search = $search ?? '';
    $movementSearch = $movementSearch ?? '';
    $totalProducts = $totalProducts ?? $products->total();
    $lowStockCount = $lowStockCount ?? $products->getCollection()->filter(fn ($product) => $product->stok <= $product->stok_minimum)->count();
    $safeStockCount = $safeStockCount ?? max($totalProducts - $lowStockCount, 0);
    $showLowStockOnly = $showLowStockOnly ?? false;
    $lastPage = $products->lastPage();
    $currentPage = $products->currentPage();
    $startPage = max(1, min($currentPage - 3, max(1, $lastPage - 6)));
    $endPage = min($lastPage, $startPage + 6);
    $movementLastPage = $movements->lastPage();
    $movementCurrentPage = $movements->currentPage();
    $movementStartPage = max(1, min($movementCurrentPage - 3, max(1, $movementLastPage - 6)));
    $movementEndPage = min($movementLastPage, $movementStartPage + 6);
@endphp

@section('page_title', 'Stok')
@section('page_subtitle', $showLowStockOnly
    ? 'Daftar produk dengan stok saat ini berada di bawah atau sama dengan batas minimum.'
    : ($isSimpleMode
        ? 'Pantau ringkasan stok barang, stok masuk/keluar, stok minimum, dan histori pergerakan stok sederhana.'
        : ($isCashier
            ? 'Cek stok barang yang tersedia untuk transaksi kasir. Halaman ini hanya untuk melihat ketersediaan stok, bukan mengelola penuh.'
            : ($canManageStock
            ? 'Pantau data stok utama, stok minimum, histori pergerakan stok, dan kontrol stok lebih detail untuk operasional gudang.'
            : 'Pantau histori stok kritis dan pergerakan barang untuk monitoring owner mode lengkap.'))))

@section('page_actions')
    @if ($canManageStock)
        <a href="{{ route('stocks.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Catat Stok Masuk
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

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <article class="rounded-2xl border {{ $showLowStockOnly ? 'border-red-200 bg-red-50/70' : 'border-[#c0c8cb] bg-white' }} p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $showLowStockOnly ? 'Produk Menipis' : 'Data Stok Utama' }}</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight {{ $showLowStockOnly ? 'text-red-600' : 'text-slate-900' }}">{{ $totalProducts }}</h2>
                <p class="mt-2 text-sm {{ $showLowStockOnly ? 'text-red-600' : 'text-slate-500' }}">{{ $showLowStockOnly ? 'Produk yang perlu segera diprioritaskan untuk restock.' : ($isCashier ? 'Produk aktif yang stoknya bisa dicek cepat oleh kasir sebelum transaksi.' : 'Produk yang sedang dimonitor pada inventory aktif.') }}</p>
            </article>
            <article class="rounded-2xl border {{ $lowStockCount > 0 ? 'border-amber-200 bg-amber-50/70' : 'border-[#c0c8cb] bg-white' }} p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Minimum</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-slate-900' }}">{{ $lowStockCount }}</h2>
                <p class="mt-2 text-sm {{ $lowStockCount > 0 ? 'text-amber-700' : 'text-slate-500' }}">{{ $isCashier ? 'Produk yang stoknya perlu diwaspadai saat melayani transaksi.' : 'Produk yang sudah mencapai atau melewati batas minimum.' }}</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isCashier ? 'Ketersediaan Aman' : ($showLowStockOnly ? 'Stok Aman' : 'Kontrol Stok Detail') }}</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $showLowStockOnly ? $safeStockCount : $movements->total() }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $isCashier ? 'Gunakan informasi ini untuk memastikan produk yang dipilih pelanggan masih tersedia.' : ($showLowStockOnly ? 'Produk lain yang saat ini masih berada pada status aman.' : ($isSimpleMode ? 'Riwayat stok masuk dan keluar yang tercatat secara sederhana.' : 'Riwayat pergerakan stok yang tercatat di sistem.')) }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-[#c0c8cb] px-6 py-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $showLowStockOnly ? 'Daftar Stok Menipis' : 'Data Stok Utama' }}</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $showLowStockOnly
                            ? 'Hanya produk dengan stok saat ini berada di bawah atau sama dengan stok minimum yang ditampilkan.'
                            : ($isSimpleMode ? 'Ringkasan stok barang dan stok minimum untuk pemantauan owner sehari-hari.' : ($isCashier ? 'Tabel stok aktif untuk membantu kasir mengecek ketersediaan produk sebelum melakukan transaksi.' : 'Tabel stok aktif, stok minimum, supplier terkait, dan indikator produk yang perlu perhatian.')) }}
                    </p>
                    @if (! $canManageStock && ! $isCashier)
                        <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-600">
                            Mode Read Only
                        </p>
                    @endif
                </div>
                <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[22rem]">
                    <form method="GET" action="{{ $showLowStockOnly ? route('stocks.notifications') : route('stocks.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <label for="stocks-search" class="sr-only">Cari stok</label>
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </span>
                            <input
                                id="stocks-search"
                                type="search"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari nama produk, kode, kategori, atau supplier..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                Cari
                            </button>
                            @if ($search !== '')
                                <a href="{{ $showLowStockOnly ? route('stocks.notifications') : route('stocks.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[920px] w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            @unless($isSimpleMode || $isCashier)
                                <th class="px-6 py-3">Supplier</th>
                            @endunless
                            <th class="px-6 py-3">Stok Saat Ini</th>
                            <th class="px-6 py-3">Stok Minimum</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($products as $product)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $product->kode_produk }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                                @unless($isSimpleMode || $isCashier)
                                    <td class="px-6 py-4 text-slate-600">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                                @endunless
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $product->stok }} {{ $product->satuan }}</p>
                                    @if ($product->stok <= $product->stok_minimum)
                                        <p class="mt-1 text-xs font-medium text-red-600">Perlu prioritas restock</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $product->stok <= $product->stok_minimum ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $product->stok <= $product->stok_minimum ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                        {{ $product->stok <= $product->stok_minimum ? 'Kritis' : 'Aman' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSimpleMode || $isCashier ? '5' : '6' }}" class="px-6 py-0">
                                    <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">{{ $showLowStockOnly ? 'Tidak ada produk dengan stok menipis.' : 'Belum ada data stok produk.' }}</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $showLowStockOnly ? 'Semua produk saat ini masih berada di atas batas minimum.' : 'Produk akan muncul di sini setelah data inventori dan pergerakan stok mulai tercatat.' }}</p>
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
                        stok
                    </div>
                </div>

                @if ($lastPage > 1)
                    <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <form method="GET" action="{{ $showLowStockOnly ? route('stocks.notifications') : route('stocks.index') }}" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
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

        @unless($isCashier)
            <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-[#c0c8cb] px-6 py-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Histori Pergerakan Stok Sederhana' : 'Histori Pergerakan Stok' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Riwayat perubahan stok masuk dan keluar yang mudah dipantau owner.' : 'Riwayat perubahan stok untuk kontrol yang lebih detail terhadap barang masuk dan keluar.' }}</p>
                    </div>
                    <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-[22rem]">
                        <form method="GET" action="{{ $showLowStockOnly ? route('stocks.notifications') : route('stocks.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            @if ($search !== '')
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif
                            <label for="movements-search" class="sr-only">Cari histori stok</label>
                            <div class="relative flex-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                </span>
                                <input
                                    id="movements-search"
                                    type="search"
                                    name="movement_search"
                                    value="{{ $movementSearch }}"
                                    placeholder="Cari produk, jenis, atau catatan..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]"
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                    Cari
                                </button>
                                @if ($movementSearch !== '')
                                    <a href="{{ $showLowStockOnly ? route('stocks.notifications', $search !== '' ? ['search' => $search] : []) : route('stocks.index', $search !== '' ? ['search' => $search] : []) }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-[1040px] w-full text-left text-sm">
                        <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Waktu</th>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Qty</th>
                                <th class="px-6 py-3">Sebelum</th>
                                <th class="px-6 py-3">Sesudah</th>
                                <th class="px-6 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($movements as $movement)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4 text-slate-600">
                                        <p>{{ $movement->tanggal_pergerakan?->format('d/m/Y') }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $movement->tanggal_pergerakan?->format('H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($canManageStock)
                                            <a href="{{ route('stocks.show', $movement) }}" class="font-semibold text-slate-900 transition hover:text-[#003441]">
                                                {{ $movement->produk?->nama_produk ?? '-' }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900">{{ $movement->produk?->nama_produk ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] {{ $movement->jenis_pergerakan === 'masuk' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ ucfirst($movement->jenis_pergerakan) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-slate-900">{{ $movement->qty }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $movement->stok_sebelum }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $movement->stok_sesudah }}</td>
                                    <td class="px-6 py-4 text-slate-600">{{ $movement->catatan ?: 'Tidak ada catatan.' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-0">
                                        <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                            <p class="text-base font-semibold text-slate-900">Belum ada histori pergerakan stok.</p>
                                            <p class="mt-2 text-sm leading-6 text-slate-500">Riwayat stok masuk dan keluar akan muncul di sini setelah transaksi stok mulai dicatat.</p>
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
                            <span class="font-semibold text-slate-700">{{ $movements->firstItem() ?? 0 }}</span>
                            -
                            <span class="font-semibold text-slate-700">{{ $movements->lastItem() ?? 0 }}</span>
                            dari
                            <span class="font-semibold text-slate-700">{{ $movements->total() }}</span>
                            histori
                        </div>
                    </div>

                    @if ($movementLastPage > 1)
                        <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <form method="GET" action="{{ $showLowStockOnly ? route('stocks.notifications') : route('stocks.index') }}" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                @if ($search !== '')
                                    <input type="hidden" name="search" value="{{ $search }}">
                                @endif
                                @if ($movementSearch !== '')
                                    <input type="hidden" name="movement_search" value="{{ $movementSearch }}">
                                @endif
                                <span>Lompat ke halaman</span>
                                <input
                                    type="number"
                                    name="movements_page"
                                    min="1"
                                    max="{{ $movementLastPage }}"
                                    value="{{ $movementCurrentPage }}"
                                    class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]"
                                >
                                <span>dari {{ $movementLastPage }}</span>
                                <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Buka
                                </button>
                            </form>

                            <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                                @if ($movements->onFirstPage())
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">
                                        Sebelumnya
                                    </span>
                                @else
                                    <a href="{{ $movements->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Sebelumnya
                                    </a>
                                @endif

                                @for ($page = $movementStartPage; $page <= $movementEndPage; $page++)
                                    @if ($page === $movementCurrentPage)
                                        <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $movements->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endfor

                                @if ($movements->hasMorePages())
                                    <a href="{{ $movements->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
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
        @endunless
    </div>
@endsection
