@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Tambah Pergerakan Stok</h3>
        <p class="mt-1 text-sm text-slate-600">Catat stok masuk manual untuk produk aktif.</p>

        <form method="POST" action="{{ route('stocks.store') }}" class="mt-6 space-y-4">
            @csrf

            <label class="block text-sm font-medium text-slate-700">
                Produk
                <select name="product_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Pilih produk</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                            {{ $product->nama_produk }} - stok {{ $product->stok }} {{ $product->satuan }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Jumlah Masuk
                <input type="number" name="qty" min="1" value="{{ old('qty') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('qty')
                    <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="block text-sm font-medium text-slate-700">
                Catatan
                <textarea name="catatan" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('catatan') }}</textarea>
            </label>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Simpan</button>
                <a href="{{ route('stocks.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
            </div>
        </form>
    </section>
@endsection
