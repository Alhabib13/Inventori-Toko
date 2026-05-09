@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Tambah Pembelian</h3>
        <p class="mt-1 text-sm text-slate-600">Catat pembelian produk dari supplier dan tambah stok otomatis.</p>

        <form method="POST" action="{{ route('purchases.store') }}" class="mt-6 space-y-6">
            @csrf

            <label class="block text-sm font-medium text-slate-700">
                Supplier
                <select name="supplier_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Pilih supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                            {{ $supplier->nama_supplier }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <div class="space-y-4">
                @foreach ($products as $index => $product)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="grid gap-4 md:grid-cols-[1fr_140px_180px] md:items-end">
                            <div>
                                <div class="font-medium text-slate-900">{{ $product->nama_produk }}</div>
                                <div class="mt-1 text-sm text-slate-500">
                                    Harga beli terakhir Rp{{ number_format((float) $product->harga_beli, 0, ',', '.') }} | stok {{ $product->stok }} {{ $product->satuan }}
                                </div>
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                            </div>

                            <label class="block text-sm font-medium text-slate-700">
                                Qty
                                <input type="number" min="0" name="items[{{ $index }}][qty]" value="{{ old('items.'.$index.'.qty', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </label>

                            <label class="block text-sm font-medium text-slate-700">
                                Harga Beli
                                <input type="number" min="0" step="0.01" name="items[{{ $index }}][harga_beli]" value="{{ old('items.'.$index.'.harga_beli', $product->harga_beli) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            @error('items')
                <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror

            <div class="grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">
                    Diskon
                    <input type="number" name="diskon" min="0" step="0.01" value="{{ old('diskon', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>

                <label class="block text-sm font-medium text-slate-700">
                    Ongkir
                    <input type="number" name="ongkir" min="0" step="0.01" value="{{ old('ongkir', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
            </div>

            <label class="block text-sm font-medium text-slate-700">
                Catatan
                <textarea name="catatan" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('catatan') }}</textarea>
            </label>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Simpan Pembelian</button>
                <a href="{{ route('purchases.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
            </div>
        </form>
    </section>
@endsection
