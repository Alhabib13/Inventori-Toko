@extends('layouts.app')

@section('page_title', 'Detail Pembelian')
@section('page_subtitle', 'Tinjau supplier, pencatat pembelian, item produk, qty, harga beli, dan total pembelian.')

@section('page_actions')
    <div class="flex flex-wrap items-center justify-end gap-3">
        @if ($canCancelPurchase)
            <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" data-confirm="Batalkan pembelian ini dan sesuaikan kembali stok produk?" data-confirm-title="Batalkan Pembelian">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg border border-red-100 bg-white px-4 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    Batalkan Pembelian
                </button>
            </form>
        @endif
        <a href="{{ route('purchases.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
            Kembali
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-6">
        @if ($errors->has('items'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                {{ $errors->first('items') }}
            </div>
        @endif

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2">
                <h3 class="text-lg font-semibold text-slate-900">{{ $purchase->kode_pembelian }}</h3>
                <p class="text-sm text-slate-600">Status pembelian {{ strtolower($purchase->status) }} pada {{ $purchase->tanggal_pembelian?->format('d/m/Y H:i') }}.</p>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Item</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $purchase->detailItem->count() }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Qty</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $purchase->detailItem->sum('qty') }}</p>
                </div>
                <div class="rounded-xl border border-[#003441] bg-[#003441] p-4 text-white">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/70">Total Pembelian</p>
                    <p class="mt-2 text-2xl font-bold">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</p>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Supplier</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $purchase->supplier?->nama_supplier ?? '-' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pencatat Pembelian</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $purchase->pengguna?->name ?? '-' }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Tanggal Pembelian</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">{{ $purchase->tanggal_pembelian?->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Subtotal</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $purchase->subtotal, 0, ',', '.') }}</dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Diskon / Ongkir</dt>
                    <dd class="mt-2 text-sm font-semibold text-slate-900">
                        Rp{{ number_format((float) $purchase->diskon, 0, ',', '.') }}
                        <span class="text-slate-400">/</span>
                        Rp{{ number_format((float) $purchase->ongkir, 0, ',', '.') }}
                    </dd>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <dt class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Pembelian</dt>
                    <dd class="mt-2 text-sm font-semibold text-[#003441]">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</dd>
                </div>
            </dl>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h3 class="text-lg font-semibold text-slate-900">Item Pembelian</h3>
                <p class="mt-1 text-sm text-slate-600">Rincian produk, qty, harga beli, dan subtotal pembelian ini.</p>
            </div>

            <div class="overflow-x-auto px-6 py-4">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Produk</th>
                            <th class="py-3 pr-4">Qty</th>
                            <th class="py-3 pr-4">Harga Beli</th>
                            <th class="py-3 pr-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($purchase->detailItem as $item)
                            <tr class="transition hover:bg-slate-50">
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-slate-900">{{ $item->nama_produk }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $item->produk?->kode_produk ?? '-' }}</p>
                                </td>
                                <td class="py-3 pr-4 font-semibold text-slate-900">{{ $item->qty }}</td>
                                <td class="py-3 pr-4">Rp{{ number_format((float) $item->harga_beli, 0, ',', '.') }}</td>
                                <td class="py-3 pr-4 font-semibold text-slate-900">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-slate-200">
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Subtotal</td>
                            <td class="py-3 pr-4 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $purchase->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Diskon</td>
                            <td class="py-3 pr-4 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $purchase->diskon, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Ongkir</td>
                            <td class="py-3 pr-4 text-sm font-semibold text-slate-900">Rp{{ number_format((float) $purchase->ongkir, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-3 pr-4 text-right text-sm font-semibold text-slate-700">Total Pembelian</td>
                            <td class="py-3 pr-4 text-sm font-bold text-[#003441]">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>
    </div>
@endsection
