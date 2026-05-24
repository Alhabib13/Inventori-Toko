@extends('layouts.app')

@section('page_title', 'Catat Stok Masuk')
@section('page_subtitle', 'Catat stok masuk manual untuk produk aktif agar data barang gudang dan histori pergerakan tetap akurat.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Panduan Stok Masuk</h2>
            <div class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Aktif</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $products->count() }}</p>
                        <p class="mt-1 text-sm text-slate-500">Pilih produk yang tepat sebelum mencatat stok masuk.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jenis Transaksi</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">Stok Masuk Manual</p>
                        <p class="mt-1 text-sm text-slate-500">Cocok untuk penerimaan barang atau penyesuaian masuk.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pilih Produk</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan produk aktif yang benar untuk menghindari kesalahan pencatatan stok pada gudang.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Audit</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Tambahkan catatan jika stok masuk berasal dari supplier tertentu, retur, atau penyesuaian manual.</p>
                </div>
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Alur Cepat</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <p>1. Pilih produk yang menerima stok masuk.</p>
                        <p>2. Isi jumlah stok dengan angka yang akurat.</p>
                        <p>3. Tambahkan catatan agar histori audit mudah ditelusuri.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('stocks.store') }}" class="space-y-6">
                @csrf

                <div class="space-y-2 border-b border-slate-200 pb-5">
                    <h2 class="text-lg font-semibold text-slate-900">Form Stok Masuk</h2>
                    <p class="text-sm leading-6 text-slate-500">Lengkapi produk, jumlah, dan catatan agar pencatatan stok tetap rapi dan konsisten.</p>
                </div>

                <div class="space-y-2">
                    <label for="stock_product" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk</label>
                    <select id="stock_product" name="product_id" class="h-11 w-full rounded-lg border {{ $errors->has('product_id') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                {{ $product->nama_produk }} - stok {{ $product->stok }} {{ $product->satuan }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500">Daftar produk hanya menampilkan produk aktif yang siap menerima penambahan stok.</p>
                    @error('product_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="stock_qty" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jumlah Masuk</label>
                    <input id="stock_qty" type="number" name="qty" min="1" value="{{ old('qty') }}" class="h-11 w-full rounded-lg border {{ $errors->has('qty') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
                    <p class="text-xs text-slate-500">Gunakan angka bulat sesuai jumlah fisik stok yang diterima.</p>
                    @error('qty')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="stock_note" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan</label>
                    <textarea id="stock_note" name="catatan" rows="4" class="w-full rounded-lg border {{ $errors->has('catatan') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">{{ old('catatan') }}</textarea>
                    <p class="text-xs text-slate-500">Contoh: barang datang dari supplier, retur pelanggan, atau koreksi manual stok.</p>
                    @error('catatan')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('stocks.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Stok Masuk
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
