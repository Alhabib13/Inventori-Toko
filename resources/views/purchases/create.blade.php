@extends('layouts.app')

@section('page_title', 'Tambah Pembelian')
@section('page_subtitle', 'Catat pembelian barang dari supplier, atur qty dan harga beli, lalu simpan sebagai stok masuk operasional gudang.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Panduan Input Pembelian</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier Aktif</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Pilih supplier terlebih dahulu, lalu isi produk yang benar-benar dibeli agar stok gudang terupdate dengan akurat.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Qty & Harga</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Biarkan qty bernilai `0` untuk produk yang tidak ikut dibeli. Harga beli bisa disesuaikan jika ada perubahan dari supplier.</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Pembelian</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan catatan untuk nomor faktur, kondisi barang, atau informasi pengiriman yang perlu diingat tim gudang.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-[1.4fr_0.6fr_0.6fr]">
                    <div class="space-y-2 md:col-span-1">
                        <label for="supplier_id" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier</label>
                        <select id="supplier_id" name="supplier_id" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
                            <option value="">Pilih supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                                    {{ $supplier->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label for="diskon" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Diskon</label>
                        <input id="diskon" type="number" name="diskon" min="0" step="0.01" value="{{ old('diskon', 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                    </div>

                    <div class="space-y-2">
                        <label for="ongkir" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Ongkir</label>
                        <input id="ongkir" type="number" name="ongkir" min="0" step="0.01" value="{{ old('ongkir', 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border border-[#c0c8cb]">
                    <div class="border-b border-[#c0c8cb] bg-[#f9f9fa] px-5 py-4">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-slate-900">Item Pembelian</h3>
                                <p class="mt-1 text-sm text-slate-500">Pilih supplier terlebih dahulu, lalu isi qty dan harga beli untuk produk yang benar-benar dibeli.</p>
                            </div>
                            <span class="rounded-full bg-[#d0e1fb]/35 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0f4c5c]" data-supplier-item-count>
                                Belum pilih supplier
                            </span>
                        </div>
                    </div>

                    <div class="space-y-3 p-4" data-purchase-items>
                        <div class="rounded-xl border border-dashed border-[#c0c8cb] bg-[#f9f9fa] px-4 py-10 text-center text-sm text-slate-500" data-purchase-empty>
                            Pilih supplier untuk menampilkan daftar item pembelian.
                        </div>
                        @foreach ($products as $index => $product)
                            <div class="hidden rounded-xl border border-slate-200 bg-white p-4" data-purchase-item data-supplier-id="{{ $product->supplier_id }}">
                                <div class="grid gap-4 lg:grid-cols-[1.2fr_130px_180px] lg:items-end">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $product->kode_produk }} • {{ $product->satuan }} • stok {{ $product->stok }}</p>
                                        <p class="mt-2 text-sm text-slate-500">
                                            Harga beli terakhir
                                            <span class="font-semibold text-slate-900">Rp{{ number_format((float) $product->harga_beli, 0, ',', '.') }}</span>
                                        </p>
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                    </div>

                                    <div class="space-y-2">
                                        <label for="purchase_qty_{{ $index }}" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Qty</label>
                                        <input id="purchase_qty_{{ $index }}" type="number" min="0" name="items[{{ $index }}][qty]" value="{{ old('items.'.$index.'.qty', 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                                    </div>

                                    <div class="space-y-2">
                                        <label for="purchase_price_{{ $index }}" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Harga Beli</label>
                                        <input id="purchase_price_{{ $index }}" type="number" min="0" step="0.01" name="items[{{ $index }}][harga_beli]" value="{{ old('items.'.$index.'.harga_beli', $product->harga_beli) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @error('items')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="space-y-2">
                    <label for="purchase_note" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan</label>
                    <textarea id="purchase_note" name="catatan" rows="4" class="w-full rounded-lg border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">{{ old('catatan') }}</textarea>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('purchases.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Pembelian
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script>
        (() => {
            const supplierField = document.getElementById('supplier_id');
            const items = Array.from(document.querySelectorAll('[data-purchase-item]'));
            const emptyState = document.querySelector('[data-purchase-empty]');
            const countBadge = document.querySelector('[data-supplier-item-count]');

            if (!supplierField || !items.length || !emptyState || !countBadge) return;

            const syncItems = () => {
                const selectedSupplier = supplierField.value;
                let visibleCount = 0;

                items.forEach((item) => {
                    const matches = selectedSupplier !== '' && item.dataset.supplierId === selectedSupplier;
                    item.classList.toggle('hidden', !matches);

                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (selectedSupplier === '') {
                    emptyState.textContent = 'Pilih supplier untuk menampilkan daftar item pembelian.';
                    emptyState.classList.remove('hidden');
                    countBadge.textContent = 'Belum pilih supplier';
                    return;
                }

                if (visibleCount === 0) {
                    emptyState.textContent = 'Belum ada produk aktif yang terhubung dengan supplier ini.';
                    emptyState.classList.remove('hidden');
                    countBadge.textContent = '0 produk';
                    return;
                }

                emptyState.classList.add('hidden');
                countBadge.textContent = `${visibleCount} produk`;
            };

            supplierField.addEventListener('change', syncItems);
            syncItems();
        })();
    </script>
@endsection
