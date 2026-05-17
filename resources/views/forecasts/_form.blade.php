@php
    $forecastProducts = \App\Models\Product::query()->orderBy('nama_produk')->get();
@endphp

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
    </div>

    <div class="space-y-2">
        <label for="periode_awal" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Awal</label>
        <input
            id="periode_awal"
            name="periode_awal"
            type="date"
            value="{{ old('periode_awal', optional($forecast->periode_awal ?? null)->format('Y-m-d')) }}"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
    </div>

    <div class="space-y-2">
        <label for="periode_akhir" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Periode Akhir</label>
        <input
            id="periode_akhir"
            name="periode_akhir"
            type="date"
            value="{{ old('periode_akhir', optional($forecast->periode_akhir ?? null)->format('Y-m-d')) }}"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
    </div>

    <div class="space-y-2">
        <label for="panjang_jendela" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jendela Moving Average</label>
        <input
            id="panjang_jendela"
            name="panjang_jendela"
            type="number"
            min="1"
            value="{{ old('panjang_jendela', $forecast->panjang_jendela ?? 7) }}"
            placeholder="Contoh: 7"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
    </div>

    <div class="space-y-2">
        <label for="prediksi_stok" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Prediksi Stok</label>
        <input
            id="prediksi_stok"
            name="prediksi_stok"
            type="number"
            min="0"
            value="{{ old('prediksi_stok', $forecast->prediksi_stok ?? '') }}"
            placeholder="Estimasi stok periode berikutnya"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
    </div>

    <div class="space-y-2">
        <label for="stok_aktual" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Aktual</label>
        <input
            id="stok_aktual"
            name="stok_aktual"
            type="number"
            min="0"
            value="{{ old('stok_aktual', $forecast->stok_aktual ?? '') }}"
            placeholder="Stok saat ini"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
    </div>

    <div class="space-y-2">
        <label for="nilai_moving_average" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nilai Moving Average</label>
        <input
            id="nilai_moving_average"
            name="nilai_moving_average"
            type="number"
            step="0.01"
            min="0"
            value="{{ old('nilai_moving_average', $forecast->nilai_moving_average ?? '') }}"
            placeholder="Contoh: 12.50"
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
    </div>

    <div class="space-y-2 md:col-span-2">
        <label for="catatan" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Analisis</label>
        <textarea
            id="catatan"
            name="catatan"
            rows="5"
            placeholder="Tulis insight singkat, rekomendasi restock, atau catatan tren penjualan."
            class="w-full rounded-lg border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        >{{ old('catatan', $forecast->catatan ?? '') }}</textarea>
    </div>
</div>
