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
    @php
        $search = $search ?? '';
        $lastPage = $purchases->lastPage();
        $currentPage = $purchases->currentPage();
        $startPage = max(1, min($currentPage - 3, max(1, $lastPage - 6)));
        $endPage = min($lastPage, $startPage + 6);
        $cancelledCount = $purchases->getCollection()->where('status', 'dibatalkan')->count();
        $activeCount = $purchases->getCollection()->count() - $cancelledCount;
        $displayTotal = $purchases->getCollection()->sum('total_bayar');
    @endphp

    <div class="space-y-6">
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pembelian Ditampilkan</p>
                <p class="mt-2 text-3xl font-bold text-[#003441]">{{ $purchases->total() }}</p>
                <p class="mt-1 text-sm text-slate-500">Ringkasan pembelian pada halaman dan filter aktif saat ini.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pembelian Aktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $activeCount }}</p>
                <p class="mt-1 text-sm text-slate-500">Transaksi pembelian yang belum dibatalkan.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-5 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Ditampilkan</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">Rp{{ number_format((float) $displayTotal, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-slate-500">Akumulasi total pembelian pada daftar yang sedang dibuka.</p>
            </article>
        </section>

        <section class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-5">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Daftar Pembelian</h3>
                    <p class="mt-1 text-sm text-slate-600">Riwayat pembelian supplier yang sudah tersimpan di sistem.</p>
                    @if (! $canManagePurchases)
                        <p class="mt-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-600">
                            Mode Read Only
                        </p>
                    @endif
                </div>

                <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <form method="GET" action="{{ route('purchases.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:min-w-[34rem] xl:grid-cols-[1fr_1fr_auto]">
                        @if ($search !== '')
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
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
                                Terapkan
                            </button>
                            <a href="{{ route('purchases.index', $search !== '' ? ['search' => $search] : []) }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                                Reset
                            </a>
                        </div>
                    </form>

                    <form method="GET" action="{{ route('purchases.index') }}" class="flex w-full flex-col gap-3 sm:flex-row sm:items-center xl:w-auto xl:min-w-[24rem]">
                        @if ($dateFrom !== '')
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                        @endif
                        @if ($dateTo !== '')
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                        @endif
                        <label for="purchase-search" class="sr-only">Cari pembelian</label>
                        <div class="relative flex-1">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </span>
                            <input id="purchase-search" type="search" name="search" value="{{ $search }}" placeholder="Cari kode, supplier, pencatat, atau status..." class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#0f4c5c] focus:bg-white focus:ring-2 focus:ring-[#d0e1fb]">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                Cari
                            </button>
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
                <table class="w-full min-w-[980px] text-left text-sm">
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
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-slate-900">{{ $purchase->supplier?->nama_supplier ?? '-' }}</p>
                                </td>
                                <td class="py-3 pr-4 text-slate-600">{{ $purchase->pengguna?->name ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">
                                    <p>{{ $purchase->tanggal_pembelian?->format('d/m/Y') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $purchase->tanggal_pembelian?->format('H:i') }}</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="font-semibold text-slate-900">Rp{{ number_format((float) $purchase->total_bayar, 0, ',', '.') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">Subtotal Rp{{ number_format((float) $purchase->subtotal, 0, ',', '.') }}</p>
                                </td>
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
                                            <form method="POST" action="{{ route('purchases.destroy', $purchase) }}" data-confirm="Batalkan pembelian ini dan sesuaikan kembali stok produk?" data-confirm-title="Batalkan Pembelian">
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
                                <td colspan="7" class="py-0">
                                    <div class="mx-auto my-8 max-w-xl rounded-2xl border border-dashed border-slate-300 bg-[#f9f9fa] px-6 py-10 text-center">
                                        <p class="text-base font-semibold text-slate-900">Belum ada pembelian yang tersimpan.</p>
                                        <p class="mt-2 text-sm leading-6 text-slate-500">Tambahkan pembelian baru untuk mulai mencatat barang masuk dari supplier dan total transaksinya.</p>
                                        @if ($canManagePurchases)
                                            <a href="{{ route('purchases.create') }}" class="mt-5 inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                                                Tambah Pembelian
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 border-t border-slate-200 pt-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="text-sm text-slate-500">
                        Menampilkan
                        <span class="font-semibold text-slate-700">{{ $purchases->firstItem() ?? 0 }}</span>
                        -
                        <span class="font-semibold text-slate-700">{{ $purchases->lastItem() ?? 0 }}</span>
                        dari
                        <span class="font-semibold text-slate-700">{{ $purchases->total() }}</span>
                        pembelian
                    </div>
                </div>

                @if ($lastPage > 1)
                    <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                            @if ($dateFrom !== '')
                                <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                            @endif
                            @if ($dateTo !== '')
                                <input type="hidden" name="date_to" value="{{ $dateTo }}">
                            @endif
                            @if ($search !== '')
                                <input type="hidden" name="search" value="{{ $search }}">
                            @endif
                            <span>Lompat ke halaman</span>
                            <input type="number" name="page" min="1" max="{{ $lastPage }}" value="{{ $currentPage }}" class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]">
                            <span>dari {{ $lastPage }}</span>
                            <button type="submit" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka</button>
                        </form>

                        <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                            @if ($purchases->onFirstPage())
                                <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Sebelumnya</span>
                            @else
                                <a href="{{ $purchases->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sebelumnya</a>
                            @endif

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                @if ($page === $currentPage)
                                    <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">{{ $page }}</span>
                                @else
                                    <a href="{{ $purchases->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($purchases->hasMorePages())
                                <a href="{{ $purchases->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Berikutnya</a>
                            @else
                                <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">Berikutnya</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
