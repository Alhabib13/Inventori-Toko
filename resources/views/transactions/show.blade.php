@extends('layouts.app')

@section('page_title', 'Detail Transaksi Penjualan')
@section('page_subtitle', 'Tinjau ringkasan transaksi, item penjualan, metode pembayaran, dan kasir pencatat.')

@section('page_actions')
    <div class="flex flex-wrap items-center justify-end gap-3">
        <button type="button" onclick="window.print()" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Print Struk
        </button>
        @if ($canCancelTransaction)
            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" data-confirm="Batalkan transaksi ini dan kembalikan stok produk?" data-confirm-title="Batalkan Transaksi">
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
    <style>
        .pos-receipt-print {
            display: none;
        }

        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }

            html,
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                background: #fff !important;
            }

            body * {
                visibility: hidden !important;
            }

            .pos-receipt-print,
            .pos-receipt-print * {
                visibility: visible !important;
            }

            .pos-receipt-print {
                position: fixed;
                left: 50%;
                top: 0;
                transform: translateX(-50%);
                display: block !important;
                box-sizing: border-box;
                width: 48mm;
                max-width: 48mm;
                padding: 3mm 0 5mm;
                color: #000;
                background: #fff;
                font-family: "Courier New", ui-monospace, monospace;
                font-size: 10px;
                line-height: 1.28;
            }

            .pos-receipt-print .receipt-center {
                text-align: center;
            }

            .pos-receipt-print .receipt-title {
                font-size: 14px;
                font-weight: 700;
                line-height: 1.15;
            }

            .pos-receipt-print .receipt-rule {
                margin: 6px 0;
                border-top: 1px dashed #000;
            }

            .pos-receipt-print .receipt-row {
                display: flex;
                justify-content: space-between;
                gap: 6px;
            }

            .pos-receipt-print .receipt-item {
                margin-bottom: 5px;
            }

            .pos-receipt-print .receipt-item-name {
                word-break: break-word;
            }

            .pos-receipt-print .receipt-total {
                font-size: 12px;
                font-weight: 700;
            }
        }
    </style>

    <div class="pos-receipt-print" aria-hidden="true">
        <div class="receipt-center">
            <div class="receipt-title">{{ $storeProfile?->store_name ?? $transaction->kasir?->store_name ?? 'Sitori POS' }}</div>
            @if (filled($storeProfile?->alamat_toko))
                <div>{{ $storeProfile->alamat_toko }}</div>
            @endif
        </div>

        <div class="receipt-rule"></div>

        <div>No: {{ $transaction->kode_transaksi }}</div>
        <div>Tgl: {{ $transaction->tanggal_transaksi?->format('d/m/Y H:i') }}</div>
        <div>Kasir: {{ $transaction->kasir?->name ?? '-' }}</div>
        <div>Pembayaran: {{ strtoupper($transaction->metode_pembayaran ?? 'tunai') }}</div>

        <div class="receipt-rule"></div>

        @foreach ($transaction->detailItem as $item)
            <div class="receipt-item">
                <div class="receipt-item-name">{{ $item->nama_produk }}</div>
                <div class="receipt-row">
                    <span>{{ $item->qty }} x Rp{{ number_format((float) $item->harga, 0, ',', '.') }}</span>
                    <span>Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
                </div>
            </div>
        @endforeach

        <div class="receipt-rule"></div>

        <div class="receipt-row">
            <span>Subtotal</span>
            <span>Rp{{ number_format((float) $transaction->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ((float) $transaction->diskon > 0)
            <div class="receipt-row">
                <span>Diskon</span>
                <span>Rp{{ number_format((float) $transaction->diskon, 0, ',', '.') }}</span>
            </div>
        @endif
        @if ((float) $transaction->pajak > 0)
            <div class="receipt-row">
                <span>Pajak</span>
                <span>Rp{{ number_format((float) $transaction->pajak, 0, ',', '.') }}</span>
            </div>
        @endif
        <div class="receipt-row receipt-total">
            <span>Total</span>
            <span>Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</span>
        </div>
        <div class="receipt-row">
            <span>Bayar</span>
            <span>Rp{{ number_format((float) $transaction->nominal_bayar, 0, ',', '.') }}</span>
        </div>
        <div class="receipt-row">
            <span>Kembali</span>
            <span>Rp{{ number_format((float) $transaction->kembalian, 0, ',', '.') }}</span>
        </div>

        <div class="receipt-rule"></div>

        <div class="receipt-center">
            Terima kasih<br>
            Barang yang sudah dibeli tidak dapat ditukar.
        </div>
    </div>

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

            <div class="space-y-4 px-6 py-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <article class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Baris Item</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $transaction->detailItem->count() }}</p>
                    </article>
                    <article class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Qty</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $transaction->detailItem->sum('qty') }}</p>
                    </article>
                    <article class="rounded-2xl border border-[#cde2e8] bg-[#eff7f8] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Penjualan</p>
                        <p class="mt-2 text-2xl font-bold text-[#003441]">Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</p>
                    </article>
                </div>

                <div class="overflow-x-auto">
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
                                    <td class="py-3 pr-4">
                                        <p class="font-medium text-slate-900">{{ $item->nama_produk }}</p>
                                        <p class="mt-1 text-xs text-slate-500">Item tercatat pada transaksi ini</p>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
                                            {{ $item->qty }} unit
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4">Rp{{ number_format((float) $item->harga, 0, ',', '.') }}</td>
                                    <td class="py-3 pr-4 font-semibold text-slate-900">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
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
            </div>
        </section>
    </div>

    @if (session('print_after_save'))
        <script data-auto-print-receipt>
            window.addEventListener('load', () => {
                @if (session('clear_pos_cart_key'))
                    window.localStorage.removeItem(@json(session('clear_pos_cart_key')));
                @endif
                window.setTimeout(() => window.print(), 350);
            });
        </script>
    @endif
@endsection
