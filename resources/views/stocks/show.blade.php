@extends('layouts.app')

@section('page_title', 'Detail Pergerakan Stok')
@section('page_subtitle', 'Lihat detail pergerakan stok untuk audit qty, stok sebelum-sesudah, referensi, dan pencatat transaksi gudang.')

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-900">{{ $stock->produk?->nama_produk ?? '-' }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($stock->jenis_pergerakan) }} pada {{ $stock->tanggal_pergerakan?->format('d/m/Y H:i') }}</p>

            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Qty Pergerakan</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-[#003441]">{{ $stock->qty }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Dicatat Oleh</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->pengguna?->name ?? '-' }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Informasi Pergerakan</h2>
                <p class="mt-1 text-sm text-slate-500">Audit lengkap untuk stok sebelum, stok sesudah, referensi, dan keterangan transaksi.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jenis Pergerakan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($stock->jenis_pergerakan) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Waktu</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->tanggal_pergerakan?->format('d/m/Y H:i') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Sebelum</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->stok_sebelum }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Sesudah</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->stok_sesudah }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Referensi</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->referensi_tipe ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $stock->catatan ?: 'Tidak ada catatan tambahan untuk pergerakan stok ini.' }}</p>
                </div>
            </div>
        </section>
    </div>
@endsection
