@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Detail Pergerakan Stok</h3>

        <dl class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Produk</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->produk?->nama_produk ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Jenis</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ ucfirst($stock->jenis_pergerakan) }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Qty</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->qty }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Dicatat Oleh</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->pengguna?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Stok Sebelum</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->stok_sebelum }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Stok Sesudah</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->stok_sesudah }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Referensi</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->referensi_tipe ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Waktu</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $stock->tanggal_pergerakan?->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>

        @if ($stock->catatan)
            <div class="mt-6">
                <h4 class="text-sm font-semibold text-slate-900">Catatan</h4>
                <p class="mt-2 text-sm text-slate-600">{{ $stock->catatan }}</p>
            </div>
        @endif
    </section>
@endsection
