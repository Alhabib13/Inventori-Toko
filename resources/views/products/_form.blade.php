@csrf

@php
    $hasCategories = $categories->isNotEmpty();
    $hasSuppliers = ! $requiresSupplier || $suppliers->isNotEmpty();
    $isFormReady = $hasCategories && $hasSuppliers;
@endphp

@if (! $hasCategories || ! $hasSuppliers)
    <div class="mb-6 space-y-4 rounded-2xl border border-amber-200 bg-amber-50 p-5">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-amber-700">Data Master Belum Lengkap</p>
            <p class="mt-2 text-sm leading-6 text-amber-800">
                {{ ! $hasCategories && ! $hasSuppliers
                    ? 'Kategori dan supplier aktif belum tersedia. Lengkapi dulu data master sebelum menambah atau memperbarui produk.'
                    : (! $hasCategories
                        ? 'Kategori aktif belum tersedia. Tambahkan kategori terlebih dahulu sebelum mengelola produk.'
                        : 'Supplier aktif belum tersedia. Tambahkan supplier terlebih dahulu sebelum mengelola produk.') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (! $hasCategories)
                <a href="{{ route('categories.create') }}" class="inline-flex h-11 items-center rounded-lg border border-amber-300 bg-white px-4 text-sm font-semibold text-amber-800 transition hover:bg-amber-100/60">
                    Tambah Kategori
                </a>
            @endif
            @if ($requiresSupplier && ! $hasSuppliers)
                <a href="{{ route('suppliers.create') }}" class="inline-flex h-11 items-center rounded-lg border border-amber-300 bg-white px-4 text-sm font-semibold text-amber-800 transition hover:bg-amber-100/60">
                    Tambah Supplier
                </a>
            @endif
        </div>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="product_name" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Produk</label>
        <input id="product_name" type="text" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk ?? '') }}" @disabled(! $isFormReady) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
        @error('nama_produk')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_unit" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Satuan</label>
        <input id="product_unit" type="text" name="satuan" value="{{ old('satuan', $product->satuan ?? 'pcs') }}" @disabled(! $isFormReady) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
        @error('satuan')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_category" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori</label>
        <select id="product_category" name="category_id" @disabled(! $hasCategories) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                    {{ $category->nama_kategori }}
                </option>
            @endforeach
        </select>
        @if (! $hasCategories)
            <p class="text-xs text-amber-700">Belum ada kategori aktif. Tambahkan kategori terlebih dahulu.</p>
        @endif
        @error('category_id')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($requiresSupplier)
        <div class="space-y-2">
            <label for="product_supplier" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier</label>
            <select id="product_supplier" name="supplier_id" @disabled(! $hasSuppliers) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                <option value="">Pilih supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id ?? '') === (string) $supplier->id)>
                        {{ $supplier->nama_supplier }}
                    </option>
                @endforeach
            </select>
            @if (! $hasSuppliers)
                <p class="text-xs text-amber-700">Belum ada supplier aktif. Tambahkan supplier terlebih dahulu.</p>
            @endif
            @error('supplier_id')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="space-y-2">
        <label for="product_buy_price" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Beli</label>
        <input id="product_buy_price" type="number" name="harga_beli" min="0" step="0.01" value="{{ old('harga_beli', $product->harga_beli ?? '') }}" @disabled(! $isFormReady) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
        @error('harga_beli')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_sell_price" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Jual</label>
        <input id="product_sell_price" type="number" name="harga_jual" min="0" step="0.01" value="{{ old('harga_jual', $product->harga_jual ?? '') }}" @disabled(! $isFormReady) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
        @error('harga_jual')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_min_stock" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Minimum</label>
        <input id="product_min_stock" type="number" name="stok_minimum" min="0" value="{{ old('stok_minimum', $product->stok_minimum ?? 0) }}" @disabled(! $isFormReady) class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
        @error('stok_minimum')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-3 rounded-xl border border-[#c0c8cb] bg-[#f9f9fa] px-4 py-3 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) @disabled(! $isFormReady) class="rounded border-slate-300 text-[#003441] focus:ring-[#003441] disabled:cursor-not-allowed">
            Status produk aktif
        </label>
    </div>

    <div class="space-y-2 md:col-span-2">
        <label for="product_description" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</label>
        <textarea id="product_description" name="deskripsi" rows="4" @disabled(! $isFormReady) class="w-full rounded-lg border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">{{ old('deskripsi', $product->deskripsi ?? '') }}</textarea>
        @error('deskripsi')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('products.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Batal
    </a>
    <button type="submit" @disabled(! $isFormReady) class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c] disabled:cursor-not-allowed disabled:bg-slate-300">
        Simpan Produk
    </button>
</div>
