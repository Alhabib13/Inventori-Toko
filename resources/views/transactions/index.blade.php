@extends('layouts.app')

@section('page_title', 'Riwayat Transaksi')
@section('page_subtitle', $isKasir
    ? 'Tinjau transaksi penjualan yang kamu catat dan gunakan filter periode untuk pencarian cepat.'
    : 'Pantau transaksi penjualan toko berdasarkan periode dan detail transaksi yang tercatat.')

@section('page_actions')
    @if ($isKasir)
        <a href="{{ route('transactions.pos') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Buka POS
        </a>
    @endif
@endsection

@section('content')
    @php
        $pageTransactions = $transactions->getCollection();
        $completedCount = $pageTransactions->where('status', 'selesai')->count();
        $cancelledCount = $pageTransactions->where('status', 'dibatalkan')->count();
        $salesTotal = $pageTransactions->sum('total_bayar');
        $filterLabel = $dateFrom || $dateTo
            ? 'Filter aktif: ' . ($dateFrom ? \Illuminate\Support\Carbon::parse($dateFrom)->format('d M Y') : 'awal') . ' - ' . ($dateTo ? \Illuminate\Support\Carbon::parse($dateTo)->format('d M Y') : 'akhir')
            : 'Belum ada filter periode aktif.';
    @endphp

    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Transaksi Tampil</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $pageTransactions->count() }}</h2>
                <p class="mt-2 text-sm text-slate-500">Riwayat transaksi pada halaman aktif sesuai filter periode.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Transaksi Selesai</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $completedCount }}</h2>
                <p class="mt-2 text-sm text-slate-500">Transaksi yang berhasil disimpan dan selesai diproses.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Penjualan</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-[#003441]">Rp{{ number_format((float) $salesTotal, 0, ',', '.') }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $cancelledCount }} transaksi dibatalkan pada data yang sedang ditampilkan.</p>
            </article>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full border border-[#cde2e8] bg-[#eff7f8] px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#003441]">
                            {{ $isKasir ? 'Transaksi Milik Saya' : 'Monitoring Owner' }}
                        </span>
                        <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-[11px] font-medium text-slate-600">
                            {{ $filterLabel }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Daftar Transaksi Penjualan</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ $isKasir ? 'Pantau transaksi yang kamu catat sendiri dan gunakan filter periode untuk pencarian cepat.' : 'Pantau transaksi penjualan toko per periode dan tinjau detail tiap transaksi dengan lebih cepat.' }}
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-[#f9f9fa] p-4 xl:min-w-[420px]">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Filter Periode</p>
                    <form method="GET" action="{{ route('transactions.index') }}" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_auto] xl:items-end">
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
                            <a href="{{ route('transactions.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if (session('status'))
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[880px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase tracking-[0.16em] text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Kode</th>
                            <th class="py-3 pr-4">Kasir</th>
                            <th class="py-3 pr-4">Tanggal</th>
                            <th class="py-3 pr-4">Item</th>
                            <th class="py-3 pr-4">Total</th>
                            <th class="py-3 pr-4">Metode</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3 pr-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr class="transition hover:bg-slate-50">
                                <td class="py-3 pr-4">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="font-semibold text-slate-900">
                                        {{ $transaction->kode_transaksi }}
                                    </a>
                                    <p class="mt-1 text-xs text-slate-500">ID transaksi penjualan</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-slate-900">{{ $transaction->kasir?->name ?? '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $isKasir ? 'Kasir aktif' : 'Pencatat transaksi' }}</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-slate-900">{{ $transaction->tanggal_transaksi?->format('d/m/Y') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $transaction->tanggal_transaksi?->format('H:i') }} WIB</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-slate-900">{{ $transaction->total_item }} item</p>
                                    <p class="mt-1 text-xs text-slate-500">Produk terjual pada transaksi ini</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="font-semibold text-[#003441]">Rp{{ number_format((float) $transaction->total_bayar, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Subtotal akhir transaksi</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700">
                                        {{ ucfirst($transaction->metode_pembayaran ?? 'tunai') }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $transaction->status === 'dibatalkan' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $transaction->status === 'dibatalkan' ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex h-9 items-center rounded-lg border border-slate-200 px-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            Detail
                                        </a>
                                        @if (auth()->user()?->role === 'owner' && $transaction->status !== 'dibatalkan')
                                            <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Batalkan transaksi ini dan kembalikan stok produk?')">
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
                                <td colspan="8" class="px-0 py-6">
                                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada transaksi pada periode ini.</p>
                                        <p class="mt-2 text-sm text-slate-500">
                                            {{ $isKasir ? 'Transaksi yang kamu catat akan muncul di sini setelah penjualan berhasil disimpan.' : 'Riwayat transaksi toko akan tampil di sini setelah ada aktivitas penjualan.' }}
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </section>
    </div>
@endsection
