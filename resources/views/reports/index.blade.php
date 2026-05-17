@extends('layouts.app')

@php
    $period = request('period', '30_hari');
    $periodLabel = match ($period) {
        '7_hari' => '7 hari terakhir',
        '90_hari' => '90 hari terakhir',
        default => '30 hari terakhir',
    };

    $salesTotal = (float) \App\Models\Transaction::query()->sum('total_bayar');
    $purchaseTotal = (float) \App\Models\Purchase::query()->sum('total_bayar');
    $stockValue = (float) \App\Models\Product::query()->selectRaw('SUM(stok * harga_beli) as total')->value('total');
    $avgMargin = $salesTotal > 0 ? max($salesTotal - $purchaseTotal, 0) / $salesTotal * 100 : 0;
@endphp

@section('page_title', 'Laporan')
@section('page_subtitle', 'Pantau laporan penjualan, pembelian, stok, filter periode, dan ringkasan performa bisnis owner mode lengkap.')

@section('page_actions')
    <form method="GET" class="flex items-center gap-3">
        <select name="period" class="h-11 rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">
            <option value="7_hari" @selected($period === '7_hari')>7 hari terakhir</option>
            <option value="30_hari" @selected($period === '30_hari')>30 hari terakhir</option>
            <option value="90_hari" @selected($period === '90_hari')>90 hari terakhir</option>
        </select>
        <button type="submit" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Terapkan Filter
        </button>
    </form>
@endsection

@section('content')
    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Laporan Penjualan</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($salesTotal, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Total penjualan untuk {{ $periodLabel }}.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Laporan Pembelian</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($purchaseTotal, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Total pembelian supplier pada periode terpilih.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Laporan Stok</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($stockValue, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">Estimasi nilai modal stok aktif saat ini.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Performa Bisnis</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($avgMargin, 1, ',', '.') }}%</h2>
                <p class="mt-2 text-sm text-slate-500">Margin kasar antara penjualan dan pembelian.</p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Ringkasan Performa Bisnis</h2>
                <div class="mt-5 space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-sm font-semibold text-slate-900">Penjualan vs Pembelian</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Gunakan perbandingan ini untuk melihat keseimbangan cashflow. Jika total pembelian tumbuh lebih cepat dari penjualan, evaluasi rotasi stok dan prioritas restock.
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-sm font-semibold text-slate-900">Rekomendasi Owner</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Fokuskan pembelian ulang pada produk dengan stok kritis dan penjualan cepat.</span></li>
                            <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Tinjau supplier dengan nilai pembelian tertinggi untuk negosiasi harga.</span></li>
                            <li class="flex gap-2"><span class="mt-2 h-1.5 w-1.5 rounded-full bg-[#003441]"></span><span>Pantau korelasi tren penjualan dengan prediksi stok agar pengadaan lebih presisi.</span></li>
                        </ul>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Filter Periode Aktif</h2>
                <div class="mt-5 space-y-4">
                    <div class="rounded-xl border border-[#003441]/10 bg-[#d0e1fb]/25 p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#0f4c5c]">Periode</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ ucfirst(str_replace('_', ' ', $periodLabel)) }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-sm font-semibold text-slate-900">Catatan</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Halaman ini disiapkan untuk owner lengkap agar evaluasi performa bisnis bisa dilakukan dari satu tempat sebelum turun ke modul stok, supplier, atau prediksi.
                        </p>
                    </div>
                </div>
            </article>
        </section>
    </div>
@endsection
