@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Tambah Transaksi Penjualan</h3>
        <p class="mt-1 text-sm text-slate-600">Pilih produk yang stoknya tersedia untuk dicatat sebagai penjualan.</p>

        <form method="POST" action="{{ route('transactions.store') }}" class="mt-6 space-y-6">
            @csrf

            <div class="space-y-4">
                @forelse ($products as $index => $product)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="font-medium text-slate-900">{{ $product->nama_produk }}</div>
                                <div class="mt-1 text-sm text-slate-500">
                                    Harga jual Rp{{ number_format((float) $product->harga_jual, 0, ',', '.') }} | stok {{ $product->stok }} {{ $product->satuan }}
                                </div>
                            </div>

                            <div class="w-28">
                                <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                <input type="number" min="0" name="items[{{ $index }}][qty]" value="{{ old('items.'.$index.'.qty', 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-slate-300 p-6 text-sm text-slate-500">
                        Belum ada produk aktif dengan stok tersedia.
                    </div>
                @endforelse
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
                    Pajak
                    <input type="number" name="pajak" min="0" step="0.01" value="{{ old('pajak', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>

                <label class="block text-sm font-medium text-slate-700">
                    Nominal Bayar
                    <input type="number" name="nominal_bayar" min="0" step="0.01" value="{{ old('nominal_bayar', 0) }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>

                <label class="block text-sm font-medium text-slate-700">
                    Metode Pembayaran
                    <input type="text" name="metode_pembayaran" value="{{ old('metode_pembayaran', 'tunai') }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </label>
            </div>

            <label class="block text-sm font-medium text-slate-700">
                Catatan
                <textarea name="catatan" rows="3" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('catatan') }}</textarea>
            </label>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Simpan Transaksi</button>
                <a href="{{ route('transactions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Batal</a>
            </div>
        </form>
    </section>
@endsection
