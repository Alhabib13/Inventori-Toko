@extends('layouts.app')

@php
    $isSimpleMode = auth()->user()?->mode_app === 'sederhana';
    $salesTotal = (float) \App\Models\Transaction::query()->sum('total_bayar');
    $purchaseTotal = (float) \App\Models\Purchase::query()->sum('total_bayar');
    $stockTotal = (int) \App\Models\Product::query()->sum('stok');
    $productCount = \App\Models\Product::query()->count();
    $activeSuppliers = \App\Models\Supplier::query()->where('is_active', true)->count();
    $criticalProducts = \App\Models\Product::query()->whereColumn('stok', '<=', 'stok_minimum')->count();
    $operationalUsers = \App\Models\User::query()->whereIn('role', ['owner', 'kasir', 'gudang'])->latest()->take(5)->get();
@endphp

@section('page_title', 'Dashboard Overview')
@section('page_subtitle', $isSimpleMode
    ? 'Ringkasan penjualan, jumlah produk, stok menipis, dan aktivitas terbaru untuk owner mode sederhana.'
    : 'Ringkasan penjualan, pembelian, stok, supplier, user operasional, dan notifikasi stok kritis untuk owner mode lengkap.')

@section('page_actions')
    <a href="{{ route('reports.index') }}" class="inline-flex h-11 items-center justify-center rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
        Ekspor Laporan
    </a>
    <a href="{{ route('products.create') }}" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
        Produk Baru
    </a>
@endsection

@section('content')
    <div class="space-y-6">
        <section class="flex items-start gap-4 rounded-2xl border border-red-100 bg-[#ffdad6] p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-[#ba1a1a]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8.25v4.5m0 3h.01M10.04 4.72 3.56 15.53A1.5 1.5 0 0 0 4.85 17.8h14.3a1.5 1.5 0 0 0 1.29-2.27L13.96 4.72a1.5 1.5 0 0 0-2.92 0Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-lg font-semibold text-[#93000a]">Notifikasi Stok Kritis</h2>
                <p class="mt-1 text-sm leading-6 text-[#93000a]/80">
                    @if ($isSimpleMode)
                        Saat ini ada {{ $criticalProducts }} produk dengan stok menipis. Prioritaskan restock untuk menjaga transaksi tetap lancar.
                    @else
                        Saat ini ada {{ $criticalProducts }} produk yang telah mencapai batas minimum stok. Prioritaskan restock pada item dengan penjualan tertinggi.
                    @endif
                </p>
            </div>
            <a href="{{ route('stocks.role-home') }}" class="inline-flex h-10 shrink-0 items-center rounded-lg bg-[#ba1a1a] px-4 text-sm font-semibold text-white transition hover:opacity-90">
                Lihat Detail
            </a>
        </section>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Ringkasan Penjualan</p>
                        <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($salesTotal, 0, ',', '.') }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0f4c5c]/10 text-[#0f4c5c]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.75v14.5M8.75 8h4.75a2.25 2.25 0 1 1 0 4.5h-3a2.25 2.25 0 1 0 0 4.5h4.75" />
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-500">Akumulasi total transaksi penjualan yang sudah tercatat di sistem.</p>
            </article>

            @if ($isSimpleMode)
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Jumlah Produk</p>
                            <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($productCount, 0, ',', '.') }}</h3>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#d0e1fb]/40 text-[#505f76]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.75 7.75 7.25-3 7.25 3M4.75 7.75 12 11l7.25-3M4.75 7.75v8.5L12 19.5l7.25-3.25v-8.5M12 11v8.5" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Jumlah produk aktif yang tersedia untuk operasional harian.</p>
                </article>
            @else
                <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Total Pembelian</p>
                            <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Rp{{ number_format($purchaseTotal, 0, ',', '.') }}</h3>
                        </div>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#d0e1fb]/40 text-[#505f76]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6.5h14l-1.25 7H6.25L5 6.5Zm0 0-.5-2H3M8 18.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm9 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Belanja supplier yang telah diproses dan masuk ke sistem inventori.</p>
                </article>
            @endif

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isSimpleMode ? 'Stok Menipis' : 'Total Stok Barang' }}</p>
                        <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $isSimpleMode ? number_format($criticalProducts, 0, ',', '.') : number_format($stockTotal, 0, ',', '.') }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#ffdcbe]/40 text-[#623d13]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.75 7.75 7.25-3 7.25 3M4.75 7.75 12 11l7.25-3M4.75 7.75v8.5L12 19.5l7.25-3.25v-8.5M12 11v8.5" />
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-500">
                    {{ $isSimpleMode ? 'Produk yang perlu diprioritaskan untuk restock sederhana.' : 'Total unit barang aktif yang sedang tersimpan di seluruh inventori.' }}
                </p>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isSimpleMode ? 'Aktivitas Terbaru' : 'Supplier Aktif' }}</p>
                        <h3 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $isSimpleMode ? $operationalUsers->count() : $activeSuppliers }}</h3>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#003441]/10 text-[#003441]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 17.5h16.5M6.5 17.5v-7.75A1.75 1.75 0 0 1 8.25 8h7.5a1.75 1.75 0 0 1 1.75 1.75v7.75M9 8V5.75h6V8" />
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-sm text-slate-500">
                    {{ $isSimpleMode ? 'Ringkasan aktivitas user terakhir yang terlibat di workspace toko.' : 'Jumlah supplier yang masih aktif mendukung pembelian operasional.' }}
                </p>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <article class="overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-[#c0c8cb] px-6 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">User Operasional</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ $isSimpleMode ? 'Aktivitas terbaru dari owner dan kasir untuk mode sederhana.' : 'Owner, kasir, dan gudang yang aktif dalam workspace toko.' }}</p>
                    </div>
                    <a href="{{ route('users.index') }}" class="text-sm font-semibold text-[#003441] transition hover:text-[#0f4c5c]">Lihat Semua</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-[#f3f4f5] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Nama User</th>
                                <th class="px-6 py-3">{{ $isSimpleMode ? 'Role User' : 'Role' }}</th>
                                <th class="px-6 py-3">Username</th>
                                <th class="px-6 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($operationalUsers as $operationalUser)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#d0e1fb] text-xs font-bold text-[#003441]">
                                                {{ strtoupper(substr($operationalUser->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $operationalUser->name }}</p>
                                                <p class="text-xs text-slate-500">{{ $operationalUser->store_name ?? 'Tim Operasional' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ ucfirst($operationalUser->role) }}</td>
                                    <td class="px-6 py-4 font-mono text-xs text-slate-500">{{ $operationalUser->username }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center gap-2 rounded-full bg-[#d0e1fb]/45 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-[#0f4c5c]">
                                            <span class="h-2 w-2 rounded-full bg-[#0f4c5c]"></span>
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-500">Belum ada user operasional.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="rounded-2xl border border-[#c0c8cb] bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">{{ $isSimpleMode ? 'Ringkasan Operasional' : 'Prioritas Hari Ini' }}</h2>
                <div class="mt-5 space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isSimpleMode ? 'Stok Menipis' : 'Stok Kritis' }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $criticalProducts }} Produk</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $isSimpleMode ? 'Pantau produk dengan stok menipis agar transaksi tetap aman.' : 'Butuh tindakan restock atau penyesuaian supplier segera.' }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $isSimpleMode ? 'Aktivitas Owner' : 'Performa Bisnis' }}</p>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $isSimpleMode
                                ? 'Gunakan laporan sederhana dan prediksi stok untuk melihat produk terlaris serta kebutuhan restock harian.'
                                : 'Gunakan halaman laporan untuk membandingkan penjualan dan pembelian per periode, lalu lanjutkan ke prediksi stok untuk menyiapkan restock mingguan.' }}
                        </p>
                    </div>
                </div>
            </article>
        </section>
    </div>
@endsection
