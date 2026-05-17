<div class="space-y-6">
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
        @error('deskripsi')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
