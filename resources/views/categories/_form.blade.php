<div class="space-y-6">
    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Pengisian</p>
        <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan nama kategori yang spesifik dan mudah dikenali agar klasifikasi produk tetap konsisten di seluruh sistem.</p>
    </div>

    <div class="space-y-2">
        <label for="nama_kategori" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Kategori</label>
        <input
            id="nama_kategori"
            name="nama_kategori"
            type="text"
            value="{{ old('nama_kategori', $category->nama_kategori ?? '') }}"
            placeholder="Contoh: Minuman, Elektronik, Sembako"
            required
            class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        />
        <p class="text-xs text-slate-500">Nama kategori akan dipakai pada form produk dan daftar inventori.</p>
        @error('nama_kategori')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="deskripsi" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Deskripsi</label>
        <textarea
            id="deskripsi"
            name="deskripsi"
            rows="5"
            placeholder="Tulis deskripsi singkat fungsi kategori ini."
            class="w-full rounded-lg border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10"
        >{{ old('deskripsi', $category->deskripsi ?? '') }}</textarea>
        <p class="text-xs text-slate-500">Deskripsi membantu tim memahami produk apa saja yang sebaiknya masuk ke kategori ini.</p>
        @error('deskripsi')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
