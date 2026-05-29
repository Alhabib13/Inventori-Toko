@extends('layouts.app')

@section('page_title', 'Point of Sale')
@section('page_subtitle', 'Kelola transaksi penjualan cepat, pilih produk, atur jumlah, pembayaran, lalu simpan ringkasan transaksi kasir.')

@section('content')
    @php
        $lastPage = $products->lastPage();
        $currentPage = $products->currentPage();
        $startPage = max(1, min($currentPage - 3, max(1, $lastPage - 6)));
        $endPage = min($lastPage, $startPage + 6);
    @endphp

    <form method="POST" action="{{ route('transactions.store') }}" class="grid grid-cols-1 gap-6 2xl:grid-cols-[0.38fr_0.62fr]">
        @csrf

        <section class="flex min-h-[680px] flex-col overflow-hidden rounded-[28px] border border-[#d3dbe0] bg-[#f4f6f7] shadow-sm lg:min-h-[760px]">
            <div class="px-5 py-5">
                <div class="mx-auto w-full max-w-[360px] rounded-[28px] border border-[#d7dfe3] bg-white px-5 py-6 shadow-[0_18px_36px_-28px_rgba(15,39,48,0.48)] sm:px-6">
                    <div class="relative border-b border-dashed border-slate-300 pb-5 text-center before:absolute before:-left-8 before:top-1/2 before:h-5 before:w-5 before:-translate-y-1/2 before:rounded-full before:bg-[#f4f6f7] before:content-[''] after:absolute after:-right-8 after:top-1/2 after:h-5 after:w-5 after:-translate-y-1/2 after:rounded-full after:bg-[#f4f6f7] after:content-['']">
                        <h2 class="text-3xl font-bold tracking-tight text-[#003441]">{{ auth()->user()?->store_name ?? 'Sitori POS' }}</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            {{ auth()->user()?->name ?? 'Kasir Aktif' }}<br>
                            {{ now()->format('d M Y') }} - {{ now()->format('H:i') }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 border-b border-dashed border-slate-300 py-4 text-xs text-slate-500">
                        <div>
                            <p class="font-semibold uppercase tracking-[0.16em] text-slate-400">Kode</p>
                            <p class="mt-1 font-mono text-sm text-slate-700">Otomatis</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold uppercase tracking-[0.16em] text-slate-400">Terminal</p>
                            <p class="mt-1 font-mono text-sm text-slate-700">Aktif</p>
                        </div>
                    </div>

                    <div class="border-b border-dashed border-slate-300 py-4">
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Struk Transaksi</p>
                            <span class="rounded-full bg-[#f3f4f5] px-3 py-1 text-xs font-semibold text-slate-600" data-selected-count>0 item</span>
                        </div>

                        <div class="grid grid-cols-[1fr_auto] text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">
                            <span>Item</span>
                            <span>Total</span>
                        </div>

                        <div class="mt-3 max-h-[250px] space-y-3 overflow-y-auto pr-1" data-order-summary>
                            <div class="px-2 py-8 text-center text-sm text-slate-500" data-order-empty>
                                Belum ada produk dipilih.
                            </div>
                        </div>

                        <div class="mt-4 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700" data-pos-item-error>
                            Pilih minimal satu item dengan qty lebih dari 0 sebelum menyimpan transaksi.
                        </div>
                    </div>

                    <div class="space-y-4 py-5 text-sm">
                        <div class="rounded-2xl border border-[#c0c8cb] bg-white p-4">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="space-y-2 font-sans">
                                    <label for="transaction_discount" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Diskon</label>
                                    <input id="transaction_discount" type="number" name="diskon" min="0" step="0.01" value="{{ old('diskon', 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" data-discount-input>
                                </div>
                                <div class="space-y-2 font-sans">
                                    <label for="transaction_tax" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Pajak</label>
                                    <input id="transaction_tax" type="number" name="pajak" min="0" step="0.01" value="{{ old('pajak', 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" data-tax-input>
                                </div>
                                <div class="space-y-2 font-sans sm:col-span-2">
                                    <label for="transaction_paid_amount" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Nominal Bayar</label>
                                    <input id="transaction_paid_amount" type="number" name="nominal_bayar" min="0" step="0.01" value="{{ old('nominal_bayar', 0) }}" class="h-11 w-full rounded-lg border border-[#c0c8cb] bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" data-paid-input>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 font-mono">
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-slate-900" data-subtotal-label>Rp0</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Diskon</span>
                                <span class="font-semibold text-slate-900" data-discount-label>Rp0</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Pajak</span>
                                <span class="font-semibold text-slate-900" data-tax-label>Rp0</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-dashed border-slate-300 pt-3">
                                <span class="font-semibold text-slate-700">Total</span>
                                <span class="text-3xl font-bold text-[#16a34a]" data-total-label>Rp0</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Bayar</span>
                                <span class="font-semibold text-slate-900" data-paid-label>Rp0</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Kembalian</span>
                                <span class="font-semibold text-emerald-700" data-change-label>Rp0</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 border-t border-dashed border-slate-300 pt-5">
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400">Metode Pembayaran</p>
                                <button type="button" class="inline-flex items-center rounded-full border border-[#c0c8cb] px-3 py-1 text-[11px] font-semibold text-slate-600 transition hover:bg-[#f3f4f5]" data-receipt-edit>
                                    Edit Struk
                                </button>
                            </div>
                            <input type="hidden" name="metode_pembayaran" value="{{ old('metode_pembayaran', 'tunai') }}" data-payment-input>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                @foreach (['tunai' => 'Tunai', 'qris' => 'QRIS'] as $paymentValue => $paymentLabel)
                                    <button
                                        type="button"
                                        class="rounded-2xl border border-[#c0c8cb] bg-white px-4 py-3 text-center text-sm font-semibold text-slate-600 transition hover:border-[#003441] hover:text-[#003441]"
                                        data-payment-option
                                        data-payment-value="{{ $paymentValue }}"
                                    >
                                        {{ $paymentLabel }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-2 font-sans">
                            <label for="transaction_note" class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Catatan</label>
                            <textarea id="transaction_note" name="catatan" rows="3" class="w-full rounded-2xl border border-[#c0c8cb] bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10">{{ old('catatan') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-dashed border-slate-300 pt-5 text-center text-xs text-slate-500">
                        Terima kasih telah berbelanja.
                        <p class="mt-1">Barang yang sudah dibeli tidak dapat ditukar.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-[#c0c8cb] bg-white px-6 py-5">
                <div class="mx-auto flex max-w-[360px] flex-col gap-3 sm:flex-row">
                    <a href="{{ route('transactions.index') }}" class="inline-flex h-12 flex-1 items-center justify-center rounded-xl border border-[#c0c8cb] text-sm font-semibold text-slate-700 transition hover:bg-[#f3f4f5]">
                        Kembali
                    </a>
                    <button type="submit" data-loading-text="Menyimpan transaksi..." class="inline-flex h-12 flex-1 items-center justify-center rounded-xl bg-[#003441] text-sm font-semibold text-white transition hover:bg-[#0f4c5c]">
                        Simpan Transaksi
                    </button>
                </div>
            </div>
        </section>

        <section class="overflow-hidden rounded-[28px] border border-[#c0c8cb] bg-white shadow-sm">
            <div class="border-b border-[#c0c8cb] px-6 py-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-[#003441]">Point of Sale</h2>
                        <p class="mt-1 text-sm text-slate-500">Cari produk aktif, atur kuantitas, lalu lanjutkan ke pembayaran.</p>
                    </div>
                    <div class="rounded-full bg-[#f3f4f5] px-4 py-2 text-sm font-medium text-slate-600" data-live-clock>
                        {{ now()->format('H:i:s') }}
                    </div>
                </div>

                <div class="mt-5">
                    <label for="pos_search" class="sr-only">Cari produk</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.75 18.5a7.75 7.75 0 1 1 0-15.5 7.75 7.75 0 0 1 0 15.5Z" />
                        </svg>
                        <input id="pos_search" type="text" placeholder="Scan barcode atau cari produk..." class="h-12 w-full rounded-2xl border border-[#c0c8cb] bg-white pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" data-product-search>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="border-b border-red-200 bg-red-50 px-6 py-4 text-sm text-red-700">
                    {{ $errors->first() }}
                    <p class="mt-1 text-xs text-red-600">Periksa qty item, stok tersedia, dan nominal pembayaran sebelum mencoba lagi.</p>
                </div>
            @endif

            <div class="px-6 py-5">
                <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-[#c0c8cb] bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Produk Aktif</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $posSummary['active_products_count'] ?? $products->count() }}</p>
                    </div>
                    <div class="rounded-2xl border border-[#c0c8cb] bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Stok Rendah</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $posSummary['low_stock_count'] ?? 0 }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">Produk yang perlu diperhatikan saat melayani transaksi.</p>
                    </div>
                    <div class="rounded-2xl border border-[#c0c8cb] bg-[#f9f9fa] p-4">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">Transaksi Hari Ini</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ $posSummary['today_transaction_count'] ?? 0 }}</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">Transaksi selesai yang sudah kamu proses hari ini.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-[24px] border border-[#d7dfe3] bg-white">
                    <div class="hidden grid-cols-[1.7fr_0.7fr_0.8fr_0.5fr] border-b border-[#d7dfe3] bg-[#f5f7f8] px-5 py-3 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500 sm:grid">
                        <div>Product</div>
                        <div>Price</div>
                        <div>Quantity</div>
                        <div>Action</div>
                    </div>

                    <div class="max-h-[560px] overflow-y-auto" data-product-list>
                        @forelse ($products as $index => $product)
                            <div class="grid grid-cols-1 gap-4 border-b border-slate-200 px-4 py-4 transition hover:bg-slate-50 last:border-b-0 sm:grid-cols-[1.7fr_0.7fr_0.8fr_0.5fr] sm:px-5" data-product-row data-search="{{ strtolower($product->nama_produk.' '.$product->kode_produk) }}">
                                <div>
                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#e4edf0] text-xs font-bold text-[#003441]">
                                            {{ strtoupper(substr($product->nama_produk, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900">{{ $product->nama_produk }}</p>
                                            <p class="mt-1 truncate text-xs text-slate-500">{{ $product->kategori?->nama_kategori ?? 'Produk aktif' }} - stok {{ $product->stok }} {{ $product->satuan }}</p>
                                            <div class="mt-2 flex flex-wrap gap-2 text-[11px]">
                                                <span class="rounded-full {{ $product->stok <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-2.5 py-1 font-semibold">
                                                    {{ $product->stok <= 5 ? 'Stok terbatas' : 'Stok aman' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:block">
                                    <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400 sm:hidden">Harga</p>
                                    <div class="font-semibold text-slate-900" data-product-price="{{ (float) $product->harga_jual }}">
                                        Rp{{ number_format((float) $product->harga_jual, 0, ',', '.') }}
                                    </div>
                                </div>
                                <div>
                                    <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400 sm:hidden">Jumlah</p>
                                    <div class="inline-flex items-center rounded-xl border border-[#d0d8dc] bg-white shadow-[0_8px_18px_-18px_rgba(15,39,48,0.5)]">
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-[#f3f4f5] hover:text-slate-800" data-qty-decrease aria-label="Kurangi jumlah">
                                            -
                                        </button>
                                        <input
                                            type="number"
                                            min="0"
                                            max="{{ $product->stok }}"
                                            name="items[{{ $index }}][qty]"
                                            value="{{ old('items.'.$index.'.qty', 0) }}"
                                            class="h-10 w-14 border-x border-[#d0d8dc] bg-white text-center text-sm font-semibold text-slate-900 outline-none"
                                            data-qty-input
                                        >
                                        <button type="button" class="inline-flex h-10 w-10 items-center justify-center text-lg text-slate-500 transition hover:bg-[#f3f4f5] hover:text-slate-800" data-qty-increase aria-label="Tambah jumlah">
                                            +
                                        </button>
                                    </div>
                                    <p class="mt-2 hidden text-xs font-medium text-red-600" data-stock-warning>
                                        Qty melebihi stok tersedia.
                                    </p>
                                </div>
                                <div>
                                    <p class="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-400 sm:hidden">Aksi</p>
                                    <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-red-500 transition hover:bg-red-50" data-qty-clear aria-label="Hapus item">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 7.75h12M9.75 7.75v8.5m4.5-8.5v8.5M8.75 4.75h6.5l.5 3H8.25l.5-3Zm-1 3h8.5l-.56 10.03a1.5 1.5 0 0 1-1.5 1.42H9.81a1.5 1.5 0 0 1-1.5-1.42L7.75 7.75Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-center text-slate-500">Belum ada produk aktif dengan stok tersedia.</div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-4 border-t border-slate-200 pt-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="text-sm text-slate-500">
                            Menampilkan
                            <span class="font-semibold text-slate-700">{{ $products->firstItem() ?? 0 }}</span>
                            -
                            <span class="font-semibold text-slate-700">{{ $products->lastItem() ?? 0 }}</span>
                            dari
                            <span class="font-semibold text-slate-700">{{ $products->total() }}</span>
                            produk
                        </div>
                    </div>

                    @if ($lastPage > 1)
                        <div class="mt-4 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                                <span>Lompat ke halaman</span>
                                <input
                                    type="number"
                                    min="1"
                                    max="{{ $lastPage }}"
                                    value="{{ $currentPage }}"
                                    class="h-10 w-20 rounded-lg border border-slate-200 bg-white px-3 text-center text-sm font-semibold text-slate-700 outline-none transition focus:border-[#0f4c5c] focus:ring-2 focus:ring-[#d0e1fb]"
                                    data-pos-page-input
                                >
                                <span>dari {{ $lastPage }}</span>
                                <button type="button" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-pos-page-jump>
                                    Buka
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-2 xl:justify-end">
                                @if ($products->onFirstPage())
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">
                                        Sebelumnya
                                    </span>
                                @else
                                    <a href="{{ $products->previousPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Sebelumnya
                                    </a>
                                @endif

                                @for ($page = $startPage; $page <= $endPage; $page++)
                                    @if ($page === $currentPage)
                                        <span class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#0f4c5c] bg-[#003441] px-3 text-sm font-semibold text-white">
                                            {{ $page }}
                                        </span>
                                    @else
                                        <a href="{{ $products->url($page) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endfor

                                @if ($products->hasMorePages())
                                    <a href="{{ $products->nextPageUrl() }}" class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Berikutnya
                                    </a>
                                @else
                                    <span class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-400">
                                        Berikutnya
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <script>
            (() => {
                const liveClock = document.querySelector('[data-live-clock]');
                const currency = new Intl.NumberFormat('id-ID');
                const form = document.querySelector('form');
                const productSearch = document.querySelector('[data-product-search]');
                const productRows = Array.from(document.querySelectorAll('[data-product-row]'));
                const discountInput = document.querySelector('[data-discount-input]');
                const taxInput = document.querySelector('[data-tax-input]');
                const paidInput = document.querySelector('[data-paid-input]');
                const summaryContainer = document.querySelector('[data-order-summary]');
                const emptySummary = document.querySelector('[data-order-empty]');
                const selectedCountLabel = document.querySelector('[data-selected-count]');
                const subtotalLabel = document.querySelector('[data-subtotal-label]');
                const discountLabel = document.querySelector('[data-discount-label]');
                const taxLabel = document.querySelector('[data-tax-label]');
                const totalLabel = document.querySelector('[data-total-label]');
                const paidLabel = document.querySelector('[data-paid-label]');
                const changeLabel = document.querySelector('[data-change-label]');
                const paymentInput = document.querySelector('[data-payment-input]');
                const paymentButtons = Array.from(document.querySelectorAll('[data-payment-option]'));
                const receiptEditButton = document.querySelector('[data-receipt-edit]');
                const posItemError = document.querySelector('[data-pos-item-error]');
                const posPageInput = document.querySelector('[data-pos-page-input]');
                const posPageJump = document.querySelector('[data-pos-page-jump]');

                const updateClock = () => {
                    if (!liveClock) {
                        return;
                    }

                    liveClock.textContent = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false,
                    }).format(new Date());
                };

                updateClock();
                setInterval(updateClock, 1000);

                const formatCurrency = (value) => `Rp${currency.format(Math.max(0, Number(value) || 0))}`;

                const updatePaymentButtons = () => {
                    paymentButtons.forEach((button) => {
                        const active = button.dataset.paymentValue === paymentInput.value;
                        button.className = `rounded-2xl border px-4 py-3 text-center text-sm font-semibold transition ${active ? 'border-[#003441] bg-[#d0e1fb]/35 text-[#003441]' : 'border-[#c0c8cb] bg-white text-slate-600 hover:border-[#003441] hover:text-[#003441]'}`;
                    });
                };

                const updateSummary = () => {
                    let subtotal = 0;
                    let selectedCount = 0;
                    const summaryItems = [];

                    productRows.forEach((row) => {
                        const qtyInput = row.querySelector('[data-qty-input]');
                        const qty = Math.max(0, Number(qtyInput.value) || 0);
                        const price = Number(row.querySelector('[data-product-price]').dataset.productPrice || 0);
                        const productName = row.querySelector('.font-semibold').textContent.trim();
                        const productMeta = row.querySelector('.text-xs').textContent.trim();

                        if (qty > 0) {
                            selectedCount += qty;
                            subtotal += qty * price;
                            summaryItems.push({ productName, productMeta, qty, price });
                        }
                    });

                    summaryContainer.querySelectorAll('[data-summary-item]').forEach((item) => item.remove());

                    if (summaryItems.length === 0) {
                        emptySummary.classList.remove('hidden');
                    } else {
                        emptySummary.classList.add('hidden');

                        summaryItems.forEach((item) => {
                            const wrapper = document.createElement('div');
                            wrapper.dataset.summaryItem = 'true';
                            wrapper.className = 'border-b border-dashed border-slate-200 px-1 pb-3 last:border-b-0';
                            wrapper.innerHTML = `
                                <div class="grid grid-cols-[1fr_auto] items-start gap-4">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">${item.productName}</p>
                                        <p class="mt-1 text-[11px] text-slate-500">${item.qty} x ${formatCurrency(item.price)}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-slate-900">${formatCurrency(item.qty * item.price)}</p>
                                        <p class="mt-1 text-[11px] text-slate-400">${item.productMeta}</p>
                                    </div>
                                </div>
                            `;
                            summaryContainer.appendChild(wrapper);
                        });
                    }

                    const discount = Math.max(0, Number(discountInput?.value) || 0);
                    const tax = Math.max(0, Number(taxInput?.value) || 0);
                    const total = Math.max(0, subtotal - discount + tax);

                    if ((Number(paidInput?.value) || 0) === 0) {
                        paidInput.value = total;
                    }

                    const paidAmount = Math.max(0, Number(paidInput?.value) || 0);
                    const changeAmount = Math.max(0, paidAmount - total);

                    selectedCountLabel.textContent = `${selectedCount} item`;
                    subtotalLabel.textContent = formatCurrency(subtotal);
                    discountLabel.textContent = formatCurrency(discount);
                    taxLabel.textContent = formatCurrency(tax);
                    totalLabel.textContent = formatCurrency(total);
                    paidLabel.textContent = formatCurrency(paidAmount);
                    changeLabel.textContent = formatCurrency(changeAmount);
                };

                productRows.forEach((row) => {
                    const qtyInput = row.querySelector('[data-qty-input]');
                    const maxQty = Number(qtyInput.max || 0);
                    const stockWarning = row.querySelector('[data-stock-warning]');

                    const syncRowWarning = () => {
                        const qty = Number(qtyInput.value) || 0;
                        if (!stockWarning) return;
                        stockWarning.classList.toggle('hidden', qty <= maxQty);
                    };

                    row.querySelector('[data-qty-increase]')?.addEventListener('click', () => {
                        qtyInput.value = Math.min(maxQty, (Number(qtyInput.value) || 0) + 1);
                        syncRowWarning();
                        updateSummary();
                    });

                    row.querySelector('[data-qty-decrease]')?.addEventListener('click', () => {
                        qtyInput.value = Math.max(0, (Number(qtyInput.value) || 0) - 1);
                        syncRowWarning();
                        updateSummary();
                    });

                    row.querySelector('[data-qty-clear]')?.addEventListener('click', () => {
                        qtyInput.value = 0;
                        syncRowWarning();
                        updateSummary();
                    });

                    qtyInput.addEventListener('input', () => {
                        if ((Number(qtyInput.value) || 0) > maxQty) qtyInput.value = maxQty;
                        if ((Number(qtyInput.value) || 0) < 0) qtyInput.value = 0;
                        syncRowWarning();
                        updateSummary();
                    });

                    syncRowWarning();
                });

                [discountInput, taxInput, paidInput].forEach((input) => input?.addEventListener('input', updateSummary));

                paymentButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        paymentInput.value = button.dataset.paymentValue;
                        updatePaymentButtons();
                    });
                });

                receiptEditButton?.addEventListener('click', () => {
                    productSearch?.focus();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });

                productSearch?.addEventListener('input', () => {
                    const keyword = productSearch.value.trim().toLowerCase();
                    productRows.forEach((row) => {
                        const haystack = row.dataset.search || '';
                        row.classList.toggle('hidden', keyword !== '' && !haystack.includes(keyword));
                    });
                });

                posPageJump?.addEventListener('click', () => {
                    const targetPage = Math.max(1, Math.min(Number(posPageInput?.max || 1), Number(posPageInput?.value || 1)));
                    const url = new URL(window.location.href);
                    url.searchParams.set('page', targetPage);
                    window.location.href = url.toString();
                });

                form?.addEventListener('submit', (event) => {
                    const hasSelectedItem = productRows.some((row) => {
                        const qtyInput = row.querySelector('[data-qty-input]');
                        return (Number(qtyInput?.value) || 0) > 0;
                    });

                    if (posItemError) {
                        posItemError.classList.toggle('hidden', hasSelectedItem);
                    }

                    if (!hasSelectedItem) {
                        event.preventDefault();
                    }
                });

                updatePaymentButtons();
                updateSummary();
            })();
        </script>
    </form>
@endsection
