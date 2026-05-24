@extends('layouts.app')

@section('page_title', 'Tambah Supplier')
@section('page_subtitle', 'Tambahkan supplier baru beserta kontak utama dan status aktifnya.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Panduan Tambah Supplier</h2>
            <div class="mt-5 space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kontak Utama</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Simpan nama kontak, telepon, dan email agar tim pembelian mudah menghubungi supplier.</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Alamat Supplier</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Alamat membantu proses kunjungan, pengiriman, dan pencatatan relasi supplier.</p>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status Awal</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Supplier baru otomatis aktif agar bisa langsung dipakai pada transaksi pembelian setelah disimpan.</p>
                </div>
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Alur Cepat</p>
                    <div class="mt-3 space-y-2 text-sm text-slate-600">
                        <p>1. Isi identitas supplier dan kontak utama.</p>
                        <p>2. Lengkapi alamat serta catatan jika perlu.</p>
                        <p>3. Simpan supplier lalu gunakan pada pembelian produk.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-lg font-semibold text-slate-900">Form Supplier Baru</h2>
                <p class="mt-1 text-sm text-slate-500">Lengkapi data supplier agar modul pembelian dan restock memiliki referensi kontak yang jelas.</p>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-6">
                @csrf

                @include('suppliers._form')

                <div class="flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-end">
                    <a href="{{ route('suppliers.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Supplier
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
