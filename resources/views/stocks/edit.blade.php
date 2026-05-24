@extends('layouts.app')

@section('page_title', 'Audit Pergerakan Stok')
@section('page_subtitle', 'Pergerakan stok tersimpan sebagai jejak audit. Gunakan halaman ini untuk meninjau detail dan catatan transaksi gudang.')

@section('page_actions')
    <a href="{{ route('stocks.show', $stock) }}" class="inline-flex h-11 items-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Lihat Detail
    </a>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.72fr_1.28fr]">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Audit Trail</h2>
            <div class="mt-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->produk?->nama_produk ?? '-' }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pergerakan</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($stock->jenis_pergerakan) }} - qty {{ $stock->qty }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="rounded-xl border border-[#d0e1fb] bg-[#d0e1fb]/28 px-4 py-4 text-sm text-[#0f4c5c]">
                Pergerakan stok tidak diubah langsung dari halaman ini agar histori gudang tetap konsisten. Jika ada kesalahan pencatatan, lakukan penyesuaian melalui transaksi stok baru dan simpan catatan koreksinya.
            </div>

            <dl class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Sebelum</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->stok_sebelum }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Sesudah</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->stok_sesudah }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pencatat</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->pengguna?->name ?? '-' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Waktu</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $stock->tanggal_pergerakan?->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4 md:col-span-2">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan</dt>
                    <dd class="mt-2 text-sm leading-6 text-slate-600">{{ $stock->catatan ?: 'Tidak ada catatan tambahan untuk pergerakan stok ini.' }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
