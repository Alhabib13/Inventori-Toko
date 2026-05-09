@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Daftar Transaksi Penjualan</h3>
        <p class="mt-1 text-sm text-slate-600">Riwayat transaksi penjualan yang sudah tersimpan.</p>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Kode</th>
                        <th class="py-3 pr-4">Kasir</th>
                        <th class="py-3 pr-4">Tanggal</th>
                        <th class="py-3 pr-4">Item</th>
                        <th class="py-3 pr-4">Total</th>
                        <th class="py-3 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="py-3 pr-4">
                                <a href="{{ route('transactions.show', $transaction) }}" class="font-medium text-slate-900">
                                    {{ $transaction->kode_transaksi }}
                                </a>
                            </td>
                            <td class="py-3 pr-4">{{ $transaction->kasir?->name ?? '-' }}</td>
                            <td class="py-3 pr-4">{{ $transaction->tanggal_transaksi?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pr-4">{{ $transaction->total_item }}</td>
                            <td class="py-3 pr-4">Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4">{{ ucfirst($transaction->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </section>
@endsection
