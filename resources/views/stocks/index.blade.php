@extends('layouts.app')

@section('content')
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold">Stok dan Pergerakan Barang</h3>
                <p class="mt-1 text-sm text-slate-600">Pantau stok produk dan riwayat perubahan stok.</p>
            </div>

            @if ($canManageStock)
                <a href="{{ route('stocks.create') }}" class="inline-flex rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                    Catat Stok Masuk
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-3 pr-4">Produk</th>
                        <th class="py-3 pr-4">Kategori</th>
                        <th class="py-3 pr-4">Supplier</th>
                        <th class="py-3 pr-4">Stok Saat Ini</th>
                        <th class="py-3 pr-4">Stok Minimum</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $product)
                        <tr>
                            <td class="py-3 pr-4">{{ $product->nama_produk }}</td>
                            <td class="py-3 pr-4">{{ $product->kategori?->nama_kategori ?? '-' }}</td>
                            <td class="py-3 pr-4">{{ $product->supplier?->nama_supplier ?? '-' }}</td>
                            <td class="py-3 pr-4">{{ $product->stok }} {{ $product->satuan }}</td>
                            <td class="py-3 pr-4">{{ $product->stok_minimum }} {{ $product->satuan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">Belum ada produk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            <h4 class="text-base font-semibold text-slate-900">Riwayat Pergerakan Stok</h4>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[840px] text-left text-sm">
                    <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Waktu</th>
                            <th class="py-3 pr-4">Produk</th>
                            <th class="py-3 pr-4">Jenis</th>
                            <th class="py-3 pr-4">Qty</th>
                            <th class="py-3 pr-4">Sebelum</th>
                            <th class="py-3 pr-4">Sesudah</th>
                            <th class="py-3 pr-4">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="py-3 pr-4">{{ $movement->tanggal_pergerakan?->format('d/m/Y H:i') }}</td>
                                <td class="py-3 pr-4">
                                    <a href="{{ route('stocks.show', $movement) }}" class="font-medium text-slate-900">
                                        {{ $movement->produk?->nama_produk ?? '-' }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4">{{ ucfirst($movement->jenis_pergerakan) }}</td>
                                <td class="py-3 pr-4">{{ $movement->qty }}</td>
                                <td class="py-3 pr-4">{{ $movement->stok_sebelum }}</td>
                                <td class="py-3 pr-4">{{ $movement->stok_sesudah }}</td>
                                <td class="py-3 pr-4">{{ $movement->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-slate-500">Belum ada pergerakan stok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        </div>
    </section>
@endsection
