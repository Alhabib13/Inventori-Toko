@csrf

<div class="grid gap-4 md:grid-cols-2">
    <label class="block text-sm font-medium text-slate-700">
        Nama Produk
        <input type="text" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        @error('nama_produk')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Satuan
        <input type="text" name="satuan" value="{{ old('satuan', $product->satuan ?? 'pcs') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        @error('satuan')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Kategori
        <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                    {{ $category->nama_kategori }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Supplier
        <select name="supplier_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Pilih supplier</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id', $product->supplier_id ?? '') === (string) $supplier->id)>
                    {{ $supplier->nama_supplier }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Harga Beli
        <input type="number" name="harga_beli" min="0" step="0.01" value="{{ old('harga_beli', $product->harga_beli ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        @error('harga_beli')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Harga Jual
        <input type="number" name="harga_jual" min="0" step="0.01" value="{{ old('harga_jual', $product->harga_jual ?? '') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        @error('harga_jual')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="block text-sm font-medium text-slate-700">
        Stok Minimum
        <input type="number" name="stok_minimum" min="0" value="{{ old('stok_minimum', $product->stok_minimum ?? 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        @error('stok_minimum')
            <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
        @enderror
    </label>

    <label class="flex items-center gap-2 self-end text-sm font-medium text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded border-slate-300">
        Aktif
    </label>
</div>

<label class="mt-4 block text-sm font-medium text-slate-700">
    Deskripsi
    <textarea name="deskripsi" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('deskripsi', $product->deskripsi ?? '') }}</textarea>
    @error('deskripsi')
        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
    @enderror
</label>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Simpan</button>
    <a href="{{ route('products.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
</div>
