@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $lowStockCount = $products->filter(fn ($product) => $product->stok <= $product->stok_minimum)->count();
@endphp

@section('page_title', 'Stok')
@section('page_subtitle', $isSimpleMode
    ? 'Pantau ringkasan stok barang, stok masuk/keluar, stok minimum, dan histori pergerakan stok sederhana.'
    : 'Pantau data stok utama, stok minimum, histori pergerakan stok, dan kontrol stok lebih detail untuk owner mode lengkap.')

@section('page_actions')
    @if ($canManageStock)
        <a href="{{ route('stocks.create') }}" class="inline-flex h-11 items-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
            Catat Stok Masuk
        </a>
    @endif
@endsection

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Data Stok Utama</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $products->count() }}</h2>
                <p class="mt-2 text-sm text-slate-500">Produk yang sedang dimonitor pada inventory aktif.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Minimum</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $lowStockCount }}</h2>
                <p class="mt-2 text-sm text-slate-500">Produk yang sudah mencapai atau melewati batas minimum.</p>
            </article>
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Kontrol Stok Detail</p>
                <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $movements->total() }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $isSimpleMode ? 'Riwayat stok masuk dan keluar yang tercatat secara sederhana.' : 'Riwayat pergerakan stok yang tercatat di sistem.' }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Data Stok Utama</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Ringkasan stok barang dan stok minimum untuk pemantauan owner sehari-hari.' : 'Tabel stok aktif, stok minimum, supplier terkait, dan indikator produk yang perlu perhatian.' }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[920px] w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Kategori</th>
                            @unless($isSimpleMode)
                                <th class="px-6 py-3">Supplier</th>
                            @endunless
                            <th class="px-6 py-3">Stok Saat Ini</th>
                            <th class="px-6 py-3">Stok Minimum</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($products as $product)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $product->kode_produk }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                                @unless($isSimpleMode)
                                    <td class="px-6 py-4 text-slate-600">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                                @endunless
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $product->stok }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-2 rounded-full {{ $product->stok <= $product->stok_minimum ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em]">
                                        <span class="h-2 w-2 rounded-full {{ $product->stok <= $product->stok_minimum ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                        {{ $product->stok <= $product->stok_minimum ? 'Kritis' : 'Aman' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSimpleMode ? '5' : '6' }}" class="px-6 py-8 text-center text-slate-500">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Histori Pergerakan Stok Sederhana' : 'Histori Pergerakan Stok' }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Riwayat perubahan stok masuk dan keluar yang mudah dipantau owner.' : 'Riwayat perubahan stok untuk kontrol yang lebih detail terhadap barang masuk dan keluar.' }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[1040px] w-full text-left text-sm">
                    <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Waktu</th>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3">Jenis</th>
                            <th class="px-6 py-3">Qty</th>
                            <th class="px-6 py-3">Sebelum</th>
                            <th class="px-6 py-3">Sesudah</th>
                            <th class="px-6 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($movements as $movement)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-6 py-4 text-slate-600">{{ $movement->tanggal_pergerakan?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('stocks.show', $movement) }}" class="font-semibold text-slate-900 transition hover:text-[#003441]">
                                        {{ $movement->produk?->nama_produk ?? '-' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ ucfirst($movement->jenis_pergerakan) }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ $movement->qty }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $movement->stok_sebelum }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $movement->stok_sesudah }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $movement->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-slate-500">Belum ada pergerakan stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-6 py-4">
                {{ $movements->links() }}
            </div>
        </section>
    </div>
@endsection
