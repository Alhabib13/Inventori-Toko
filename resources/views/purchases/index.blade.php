@extends('layouts.app')

@section('page_title', 'Riwayat Pembelian')
@section('page_subtitle', $isOwner
    ? 'Pantau pembelian supplier untuk kebutuhan monitoring owner lengkap.'
    : 'Tinjau pembelian supplier dan gunakan filter periode untuk operasional gudang.')

@section('page_actions')
    @if ($canManagePurchases)
        <a href="{{ route('purchases.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Tambah Pembelian
        </a>
    @endif
@endsection

@section('content')
    <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Daftar Pembelian</h3>
                <p class="mt-1 text-sm text-slate-600">Riwayat pembelian supplier yang sudah tersimpan di sistem.</p>
            </div>

            <form method="GET" action="{{ route('purchases.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="space-y-2">
                    <label for="date_from" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Tanggal Mulai</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                </div>
                <div class="space-y-2">
                    <label for="date_to" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Tanggal Akhir</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Filter
                    </button>
                    <a href="{{ route('purchases.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase tracking-[0.16em] text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Kode</th>
                        <th class="py-3 pr-4">Supplier</th>
                        <th class="py-3 pr-4">Dicatat Oleh</th>
                        <th class="py-3 pr-4">Tanggal</th>
                        <th class="py-3 pr-4">Total</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        <tr class="transition hover:bg-slate-50">
                            <td class="py-3 pr-4">
                                <a href="{{ route('purchases.show', $purchase) }}" class="font-medium text-slate-900">
                                    {{ $purchase->kode_pembelian }}
                                </a>
                            </td>
                            <td class="py-3 pr-4">{{ $purchase->supplier?->nama_supplier ?? '-' }}</td>
                            <td class="py-3 pr-4">{{ $purchase->pengguna?->name ?? '-' }}</td>
                            <td class="py-3 pr-4">{{ $purchase->tanggal_pembelian?->format('d/m/Y H:i') }}</td>
                            <td class="py-3 pr-4">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex items-center gap-2 rounded-full {{ $purchase->status === 'dibatalkan' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                    <span class="h-2 w-2 rounded-full {{ $purchase->status === 'dibatalkan' ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                    {{ ucfirst($purchase->status) }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                        Detail
                                    </a>
                                    @if ($canManagePurchases && $purchase->status !== 'dibatalkan')
                                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Batalkan pembelian ini dan sesuaikan kembali stok produk?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 items-center rounded-lg border border-red-100 px-3 text-sm font-medium text-red-600 transition hover:bg-red-50">
                                                Batalkan
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-500">Belum ada pembelian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    </section>
@endsection
