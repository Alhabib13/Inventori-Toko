<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="space-y-2 md:col-span-2">
        <label for="product_id" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk</label>
        <select
            id="product_id"
            name="product_id"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        >
            <option value="">Pilih produk untuk prediksi stok</option>
            @foreach ($forecastProducts as $forecastProduct)
                <option value="{{ $forecastProduct->id }}" @selected((string) old('product_id', $forecast->product_id ?? '') === (string) $forecastProduct->id)>
                    {{ $forecastProduct->nama_produk }} ({{ $forecastProduct->kode_produk }})
                </option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500">Pilih satu produk yang ingin dianalisis berdasarkan histori transaksi penjualannya.</p>
        @error('product_id')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="periode_akhir" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Akhir</label>
        <input
            id="periode_akhir"
            name="periode_akhir"
            type="date"
            value="{{ old('periode_akhir', optional($forecast->periode_akhir ?? now())->format('Y-m-d')) }}"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
        <p class="text-xs text-slate-500">Gunakan tanggal akhir periode analisis yang ingin dijadikan acuan forecast.</p>
        @error('periode_akhir')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="panjang_jendela" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jendela Moving Average</label>
        <select
            id="panjang_jendela"
            name="panjang_jendela"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        >
            @foreach ([1, 2, 3, 6, 12] as $windowOption)
                <option value="{{ $windowOption }}" @selected((string) old('panjang_jendela', $forecast->panjang_jendela ?? 3) === (string) $windowOption)>
                    {{ $windowOption }} bulan
                </option>
            @endforeach
        </select>
        <p class="text-xs text-slate-500">Pilih jumlah bulan histori yang ingin digunakan. Rekomendasi awal: 3 bulan.</p>
        @error('panjang_jendela')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2 md:col-span-2">
        <label for="catatan" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Analisis</label>
        <textarea
            id="catatan"
            name="catatan"
            rows="5"
            placeholder="Tulis insight singkat, asumsi analisis, atau catatan rekomendasi restock."
            class="w-full rounded-lg border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        >{{ old('catatan', $forecast->catatan ?? '') }}</textarea>
        @error('catatan')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
