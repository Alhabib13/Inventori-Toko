@csrf

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="space-y-2">
        <label for="product_name" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Produk</label>
        <input id="product_name" type="text" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk ?? '') }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
        @error('nama_produk')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_unit" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Satuan</label>
        <input id="product_unit" type="text" name="satuan" value="{{ old('satuan', $product->satuan ?? 'pcs') }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
        @error('satuan')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_category" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kategori</label>
        <select id="product_category" name="category_id" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                    {{ $category->nama_kategori }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($requiresSupplier)
        <div class="space-y-2">
            <label for="product_supplier" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier</label>
            <select id="product_supplier" name="supplier_id" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
                <option value="">Pilih supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id ?? '') === (string) $supplier->id)>
                        {{ $supplier->nama_supplier }}
                    </option>
                @endforeach
            </select>
            @error('supplier_id')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div class="space-y-2">
        <label for="product_buy_price" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Beli</label>
        <input id="product_buy_price" type="number" name="harga_beli" min="0" step="0.01" value="{{ old('harga_beli', $product->harga_beli ?? '') }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
        @error('harga_beli')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_sell_price" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Jual</label>
        <input id="product_sell_price" type="number" name="harga_jual" min="0" step="0.01" value="{{ old('harga_jual', $product->harga_jual ?? '') }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
        @error('harga_jual')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="product_min_stock" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Minimum</label>
        <input id="product_min_stock" type="number" name="stok_minimum" min="0" value="{{ old('stok_minimum', $product->stok_minimum ?? 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
        @error('stok_minimum')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-end">
        <label class="inline-flex items-center gap-3 rounded-xl border border-[#c0c8cb] bg-[#f9f9fa] px-4 py-3 text-sm font-medium text-slate-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded border-slate-300 text-[#003441] focus:ring-[#003441]">
            Status produk aktif
        </label>
    </div>

    <div class="space-y-2 md:col-span-2">
        <label for="product_description" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</label>
        <textarea id="product_description" name="deskripsi" rows="4" class="w-full rounded-lg border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">{{ old('deskripsi', $product->deskripsi ?? '') }}</textarea>
        @error('deskripsi')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
    <a href="{{ route('products.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Batal
    </a>
    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Simpan Produk
    </button>
</div>
