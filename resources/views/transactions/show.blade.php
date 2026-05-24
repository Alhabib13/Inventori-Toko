@extends('layouts.app')

@section('page_title', 'Detail Transaksi Penjualan')
@section('page_subtitle', 'Tinjau ringkasan transaksi, item penjualan, metode pembayaran, dan kasir pencatat.')

@section('page_actions')
    <div class="flex flex-wrap items-center justify-end gap-3">
        @if ($canCancelTransaction)
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Batalkan transaksi ini dan kembalikan stok produk?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-red-100 bg-white px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    Batalkan Transaksi
                </button>
            </form>
        @endif
        <a href="{{ route('transactions.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
            Kembali
        </a>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[0.36fr_0.64fr]">
        <section class="rounded-[28px] border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-5">
                <h3 class="text-xl font-semibold text-slate-900">Ringkasan Struk</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $transaction->kode_transaksi }} - {{ $transaction->tanggal_transaksi?->format('d/m/Y H:i') }}</p>
            </div>

            <div class="space-y-5 px-6 py-5">
                @if (session('status'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                        <p class="font-semibold">Transaksi berhasil diproses.</p>
                        <p class="mt-1">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Status Transaksi</p>
                    <p class="mt-2 text-sm font-semibold {{ $transaction->status === 'dibatalkan' ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ ucfirst($transaction->status) }}
                    </p>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Kasir</span>
                        <span class="font-semibold text-slate-900">{{ $transaction->kasir?->name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Metode</span>
                        <span class="font-semibold text-slate-900">{{ ucfirst($transaction->metode_pembayaran ?? 'tunai') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Total Item</span>
                        <span class="font-semibold text-slate-900">{{ $transaction->total_item }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Nominal Bayar</span>
                        <span class="font-semibold text-slate-900">Rp{{ number_format((float) $transaction->nominal_bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Kembalian</span>
                        <span class="font-semibold text-emerald-700">Rp{{ number_format((float) $transaction->kembalian, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-900">Rp{{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Diskon</span>
                            <span class="font-semibold text-slate-900">Rp{{ number_format((float) $transaction->diskon, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Pajak</span>
                            <span class="font-semibold text-slate-900">Rp{{ number_format((float) $transaction->pajak, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                            <span class="font-semibold text-slate-700">Total Bayar</span>
                            <span class="text-xl font-bold text-[#003441]">Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-5">
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
