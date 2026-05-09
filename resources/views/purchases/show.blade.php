@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2">
            <h3 class="text-lg font-semibold">Detail Pembelian</h3>
            <p class="text-sm text-slate-600">{{ $purchase->kode_pembelian }}</p>
        </div>

        <dl class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Supplier</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $purchase->supplier?->nama_supplier ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Dicatat Oleh</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $purchase->pengguna?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Tanggal</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $purchase->tanggal_pembelian?->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold uppercase text-slate-500">Total Bayar</dt>
                <dd class="mt-1 text-sm text-slate-900">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Produk</th>
                        <th class="py-3 pr-4">Qty</th>
                        <th class="py-3 pr-4">Harga Beli</th>
                        <th class="py-3 pr-4">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($purchase->detailItem as $item)
                        <tr>
                            <td class="py-3 pr-4">{{ $item->nama_produk }}</td>
                            <td class="py-3 pr-4">{{ $item->qty }}</td>
                            <td class="py-3 pr-4">Rp{{ number_format((float) $item->harga_beli, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
