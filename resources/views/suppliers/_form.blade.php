@php
    $supplier = $supplier ?? null;
@endphp

@if ($errors->any())
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
        <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0 1 18 0z" />
        </svg>
        <div>
            <h2 class="text-sm font-bold">Gagal Menyimpan Supplier</h2>
            <p class="mt-1 text-sm">{{ $errors->first() }}</p>
        </div>
    </div>
@endif

<div class="mb-6 rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan Pengisian</p>
    <p class="mt-2 text-sm leading-6 text-slate-600">Lengkapi nama supplier, kontak utama, dan alamat yang valid agar tim pembelian mudah menghubungi supplier saat restock.</p>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div class="space-y-2">
        <label for="nama_supplier" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Supplier</label>
        <input id="nama_supplier" name="nama_supplier" type="text" value="{{ old('nama_supplier', $supplier?->nama_supplier) }}" required class="h-11 w-full rounded-lg border {{ $errors->has('nama_supplier') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
        @error('nama_supplier')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="nama_kontak" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nama Kontak</label>
        <input id="nama_kontak" name="nama_kontak" type="text" value="{{ old('nama_kontak', $supplier?->nama_kontak) }}" required class="h-11 w-full rounded-lg border {{ $errors->has('nama_kontak') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
        @error('nama_kontak')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="email" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $supplier?->email) }}" class="h-11 w-full rounded-lg border {{ $errors->has('email') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
        @error('email')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="telepon" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Telepon</label>
        <input id="telepon" name="telepon" type="text" value="{{ old('telepon', $supplier?->telepon) }}" required class="h-11 w-full rounded-lg border {{ $errors->has('telepon') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
        @error('telepon')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="space-y-2">
    <label for="alamat" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Alamat</label>
    <textarea id="alamat" name="alamat" rows="4" required class="w-full rounded-lg border {{ $errors->has('alamat') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">{{ old('alamat', $supplier?->alamat) }}</textarea>
    @error('alamat')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label for="keterangan" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Keterangan</label>
    <textarea id="keterangan" name="keterangan" rows="3" class="w-full rounded-lg border {{ $errors->has('keterangan') ? 'border-red-300 bg-red-50/60' : 'border-[#c0c8cb]' }} bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">{{ old('keterangan', $supplier?->keterangan) }}</textarea>
    @error('keterangan')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@if ($supplier)
    <div class="space-y-3">
        <label class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status Supplier</label>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label class="relative cursor-pointer">
                <input type="radio" name="is_active" value="1" class="peer sr-only" @checked((string) old('is_active', $supplier->is_active ? '1' : '0') === '1')>
                <div class="rounded-xl border border-[#c0c8cb] bg-white p-4 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:bg-[#f9f9fa]">
                    <p class="font-semibold text-slate-900">Aktif</p>
                    <p class="mt-1 text-sm text-slate-500">Supplier bisa dipakai untuk transaksi pembelian.</p>
                </div>
            </label>
            <label class="relative cursor-pointer">
                <input type="radio" name="is_active" value="0" class="peer sr-only" @checked((string) old('is_active', $supplier->is_active ? '1' : '0') === '0')>
                <div class="rounded-xl border border-[#c0c8cb] bg-white p-4 transition peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:bg-[#f9f9fa]">
                    <p class="font-semibold text-slate-900">Nonaktif</p>
                    <p class="mt-1 text-sm text-slate-500">Supplier disimpan, tetapi tidak dipakai untuk pembelian baru.</p>
                </div>
            </label>
        </div>
    </div>
@endif
