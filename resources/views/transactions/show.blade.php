@extends('layouts.app')

@section('page_title', 'Detail Transaksi Penjualan')
@section('page_subtitle', 'Tinjau ringkasan transaksi, item penjualan, metode pembayaran, dan kasir pencatat.')

@section('page_actions')
    <a href="{{ route('transactions.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Kembali
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2">
                <h3 class="text-lg font-semibold text-slate-900">{{ $transaction->kode_transaksi }}</h3>
                <p class="text-sm text-slate-600">Status transaksi {{ strtolower($transaction->status) }} pada {{ $transaction->tanggal_transaksi?->format('d/m/Y H:i') }}.</p>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kasir Pencatat</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $transaction->kasir?->name ?? '-' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Metode Pembayaran</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($transaction->metode_pembayaran ?? 'tunai') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Item</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $transaction->total_item }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Subtotal</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Bayar</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nominal Bayar / Kembalian</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">
                        Rp{{ number_format((float) $transaction->nominal_bayar, 0, ',', '.') }}
                        <span class="text-slate-400">/</span>
                        Rp{{ number_format((float) $transaction->kembalian, 0, ',', '.') }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Item Penjualan</h3>
                <p class="mt-1 text-sm text-slate-600">Rincian produk, qty, harga jual, dan subtotal pada transaksi ini.</p>
            </div>

            <div class="overflow-x-auto px-6 py-4">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Produk</th>
                            <th class="py-3 pr-4">Qty</th>
                            <th class="py-3 pr-4">Harga</th>
                            <th class="py-3 pr-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaction->detailItem as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="py-3 pr-4 font-medium text-slate-900">{{ $item->nama_produk }}</td>
                                <td class="py-3 pr-4">{{ $item->qty }}</td>
                                <td class="py-3 pr-4">Rp{{ number_format((float) $item->harga, 0, ',', '.') }}</td>
                                <td class="py-3 pr-4">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Subtotal</td>
                            <td class="py-3 pr-4 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Diskon</td>
                            <td class="py-3 pr-4 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $transaction->diskon, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Pajak</td>
                            <td class="py-3 pr-4 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $transaction->pajak, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Total Bayar</td>
                            <td class="py-3 pr-4 text-sm font-bold text-[#003441]">Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
@endsection
