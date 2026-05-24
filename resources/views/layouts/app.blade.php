<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judulHalaman ?? 'Sitori' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [data-sidebar-panel],
        [data-sidebar-overlay] {
            transition: transform 0.22s ease, opacity 0.22s ease, width 0.22s ease;
        }

        [data-sidebar-label],
        [data-sidebar-brand-copy],
        [data-sidebar-logout-label] {
            transition: opacity 0.18s ease;
        }

        @media (max-width: 767.98px) {
            [data-sidebar-root][data-sidebar-state='closed'] [data-sidebar-panel] {
                transform: translateX(-100%);
            }

            [data-sidebar-root][data-sidebar-state='closed'] [data-sidebar-overlay] {
                opacity: 0;
                pointer-events: none;
            }

            [data-sidebar-root][data-sidebar-state='open'] [data-sidebar-panel] {
                transform: translateX(0);
            }

            [data-sidebar-root][data-sidebar-state='open'] [data-sidebar-overlay] {
                opacity: 1;
                pointer-events: auto;
            }
        }

        @media (min-width: 768px) {
            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-panel] {
                width: 5.5rem;
                min-width: 5.5rem;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-brand] {
                justify-content: center;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-brand-leading] {
                display: none;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-brand-copy],
            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-label],
            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-logout-label] {
                opacity: 0;
                pointer-events: none;
                width: 0;
                overflow: hidden;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-link] {
                justify-content: center;
                gap: 0;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                min-height: 3rem;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-header] {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-footer] {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-nav] {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-toggle] {
                margin-inline: auto;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-logout] {
                justify-content: center;
                gap: 0;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-logout-icon] {
                margin-inline: auto;
            }

            [data-sidebar-root][data-sidebar-state='collapsed'] [data-sidebar-link].is-active {
                border-left-width: 0;
                border-width: 1px;
                border-color: rgba(255, 255, 255, 0.18);
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.1));
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f9f9fa] font-sans text-[#191c1d] antialiased">
    @php
        $user = auth()->user();
        $role = $user?->role;
        $modeApp = $user?->mode_app;
        $criticalProductsCount = in_array($role, ['owner', 'gudang'], true)
            ? \App\Models\Product::query()->whereColumn('stok', '<=', 'stok_minimum')->count()
            : 0;
        $criticalProductsPreview = $criticalProductsCount > 0
            ? \App\Models\Product::query()
                ->whereColumn('stok', '<=', 'stok_minimum')
                ->orderByRaw('(stok_minimum - stok) DESC')
                ->take(5)
                ->get(['id', 'nama_produk', 'stok', 'stok_minimum', 'satuan'])
            : collect();

        $sidebarLinks = match ($role) {
            'owner' => $modeApp === 'sederhana'
                ? [
                    ['label' => 'Dashboard', 'route' => 'dashboard.index', 'icon' => 'dashboard'],
                    ['label' => 'Produk', 'route' => 'products.index', 'icon' => 'products'],
                    ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'categories'],
                    ['label' => 'Stok', 'route' => 'stocks.role-home', 'icon' => 'stocks'],
                    ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'reports'],
                    ['label' => 'Prediksi Stok', 'route' => 'forecasts.index', 'icon' => 'forecast'],
                    ['label' => 'Manajemen User', 'route' => 'users.index', 'icon' => 'users'],
                    ['label' => 'Register User', 'route' => 'users.register', 'icon' => 'user-add'],
                ]
                : [
                    ['label' => 'Dashboard', 'route' => 'dashboard.index', 'icon' => 'dashboard'],
                    ['label' => 'Produk', 'route' => 'products.index', 'icon' => 'products'],
                    ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'categories'],
                    ['label' => 'Stok', 'route' => 'stocks.role-home', 'icon' => 'stocks'],
                    ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'reports'],
                    ['label' => 'Prediksi Stok', 'route' => 'forecasts.index', 'icon' => 'forecast'],
                    ['label' => 'Manajemen User', 'route' => 'users.index', 'icon' => 'users'],
                    ['label' => 'Register User', 'route' => 'users.register', 'icon' => 'user-add'],
                ],
            'gudang' => $modeApp === 'lengkap'
                ? [
                    ['label' => 'Stok', 'route' => 'stocks.role-home', 'icon' => 'stocks'],
                    ['label' => 'Produk', 'route' => 'products.index', 'icon' => 'products'],
                    ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'categories'],
                    ['label' => 'Supplier', 'route' => 'suppliers.index', 'icon' => 'supplier'],
                    ['label' => 'Pembelian', 'route' => 'purchases.index', 'icon' => 'purchase'],
                    ['label' => 'Prediksi Stok', 'route' => 'forecasts.index', 'icon' => 'forecast'],
                ]
                : [],
            'kasir' => [
                ['label' => 'POS', 'route' => 'transactions.pos', 'icon' => 'cashier'],
                ['label' => 'Riwayat Transaksi', 'route' => 'transactions.index', 'icon' => 'reports'],
                ['label' => 'Stok Barang', 'route' => 'stocks.role-home', 'icon' => 'stocks'],
            ],
            default => [],
        };

        $iconMap = [
            'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.5 12 4l9 9M5.25 12v7.25A1.75 1.75 0 0 0 7 21h3.75v-5.75h2.5V21H17a1.75 1.75 0 0 0 1.75-1.75V12" />',
            'reports' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 4.75h10.5a.75.75 0 0 1 .75.75v13a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75v-13a.75.75 0 0 1 .75-.75ZM9 9h6M9 12.5h6M9 16h3.5" />',
            'forecast' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 18.5h14.5M7.5 15l3-3 2.25 2.25L17 9.5M14.5 9.5H17v2.5" />',
            'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 18.5v-.75A3.75 3.75 0 0 0 12.75 14h-3.5A3.75 3.75 0 0 0 5.5 17.75v.75M14.25 7.75a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM19 18.5v-.5A3 3 0 0 0 16 15M16 8.25a2 2 0 1 0 0-4" />',
            'user-add' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.5 18.5v-.75A3.75 3.75 0 0 0 11.75 14h-3.5A3.75 3.75 0 0 0 4.5 17.75v.75M13.25 7.75a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM18 8v4M16 10h4" />',
            'products' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.75 7.75 7.25-3 7.25 3M4.75 7.75 12 11l7.25-3M4.75 7.75v8.5L12 19.5l7.25-3.25v-8.5M12 11v8.5" />',
            'categories' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.75 6.5h12.5M5.75 12h12.5M5.75 17.5h12.5" />',
            'stocks' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 18.25V8.75A1.75 1.75 0 0 1 6.75 7h10.5A1.75 1.75 0 0 1 19 8.75v9.5M8.5 18.25V11h7v7.25M3.75 18.25h16.5" />',
            'supplier' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 17.5h16.5M6.5 17.5v-7.75A1.75 1.75 0 0 1 8.25 8h7.5a1.75 1.75 0 0 1 1.75 1.75v7.75M9 8V5.75h6V8" />',
            'purchase' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 6.5h14l-1.25 7H6.25L5 6.5Zm0 0-.5-2H3M8 18.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm9 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />',
            'alert' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8.25v4.5m0 3h.01M10.04 4.72 3.56 15.53A1.5 1.5 0 0 0 4.85 17.8h14.3a1.5 1.5 0 0 0 1.29-2.27L13.96 4.72a1.5 1.5 0 0 0-2.92 0Z" />',
            'cashier' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6.75h12a1 1 0 0 1 1 1v8.5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-8.5a1 1 0 0 1 1-1Zm2.25 7.5h3.5m4.25 0h.01M8.75 10.25h6.5" />',
        ];

        $pageTitle = trim($__env->yieldContent('page_title')) ?: ($judulHalaman ?? 'Dashboard');
        $pageSubtitle = trim($__env->yieldContent('page_subtitle')) ?: 'Workspace inventori untuk operasional toko mode lengkap.';
    @endphp

    <div class="flex min-h-screen bg-[#f9f9fa]" data-sidebar-root data-sidebar-state="open">
        <div class="fixed inset-0 z-30 bg-slate-950/35 opacity-0 md:hidden" data-sidebar-overlay></div>

        <aside class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 shrink-0 flex-col overflow-hidden border-r border-[#163946] bg-gradient-to-b from-[#0f2730] via-[#123743] to-[#163f4d] text-white md:sticky md:top-0 md:z-auto" data-sidebar-panel>
            <div class="border-b border-[#c0c8cb] px-6 py-5" data-sidebar-header>
                <div class="flex items-center justify-between gap-3" data-sidebar-brand>
                    <div class="flex items-center gap-3" data-sidebar-brand-leading>
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.75 7.75 7.25-3 7.25 3M4.75 7.75 12 11l7.25-3M4.75 7.75v8.5L12 19.5l7.25-3.25v-8.5M12 11v8.5" />
                            </svg>
                        </div>
                        <div data-sidebar-brand-copy>
                            <p class="text-2xl font-extrabold tracking-tight text-white">Sitori</p>
                            <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-300">{{ $modeApp === 'lengkap' ? 'Mode Lengkap' : 'Workspace Toko' }}</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/8 text-white transition hover:bg-white/14"
                        data-sidebar-toggle
                        aria-label="Toggle sidebar"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.75 7.5h14.5M4.75 12h14.5M4.75 16.5h14.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" data-sidebar-nav>
                @foreach ($sidebarLinks as $link)
                    @php
                        $isActive = request()->routeIs($link['route']) || ($link['route'] === 'stocks.role-home' && request()->routeIs('stocks.*'));
                    @endphp
                    <a
                        href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition {{ $isActive ? 'is-active border-l-4 border-[#8ac7d8] bg-white/12 font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.04)]' : 'text-slate-200 hover:bg-white/8 hover:text-white' }}"
                        data-sidebar-link
                        title="{{ $link['label'] }}"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $iconMap[$link['icon']] ?? $iconMap['dashboard'] !!}
                        </svg>
                        <span data-sidebar-label>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto border-t border-white/12 bg-[#102d37]/92 px-3 py-4 backdrop-blur" data-sidebar-footer>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl border border-white/55 bg-white/6 px-4 py-3 text-sm font-semibold text-white shadow-[inset_0_1px_0_rgba(255,255,255,0.08)] transition hover:bg-white/12" data-sidebar-logout title="Keluar">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/30 bg-[#f4f8f9] text-[#123743]" data-sidebar-logout-icon>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.75 7.75V6.5A1.75 1.75 0 0 1 12.5 4.75h4A1.75 1.75 0 0 1 18.25 6.5v11A1.75 1.75 0 0 1 16.5 19.25h-4a1.75 1.75 0 0 1-1.75-1.75v-1.25M14 12H4.75m0 0 2.75-2.75M4.75 12l2.75 2.75" />
                            </svg>
                        </span>
                        <span data-sidebar-logout-label>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-[#d6dde1] bg-[#f8fafb]/96 shadow-[0_10px_28px_-22px_rgba(15,39,48,0.48)] backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4">
                        <div class="relative hidden max-w-md sm:block">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.75 18.5a7.75 7.75 0 1 1 0-15.5 7.75 7.75 0 0 1 0 15.5Z" />
                            </svg>
                            <input type="text" placeholder="Cari produk, kategori, supplier, atau user..." class="h-12 w-full rounded-full border border-[#d2dadd] bg-white pl-11 pr-4 text-sm text-slate-700 shadow-[0_8px_18px_-16px_rgba(15,39,48,0.55)] outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                        </div>
                        <div class="sm:hidden">
                            <p class="text-xl font-extrabold tracking-tight text-[#003441]">Sitori</p>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $modeApp === 'lengkap' ? 'Mode Lengkap' : 'Workspace Toko' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 rounded-full border border-[#d8dee2] bg-white/88 px-2 py-1.5 shadow-[0_12px_28px_-22px_rgba(15,39,48,0.55)] sm:gap-3 sm:px-3">
                        <details class="relative">
                            <summary
                                class="relative flex cursor-pointer list-none rounded-full p-2.5 text-slate-500 transition hover:bg-[#f3f4f5] hover:text-slate-800"
                                aria-label="Notifikasi stok"
                                title="{{ $criticalProductsCount > 0 ? $criticalProductsCount . ' stok perlu perhatian' : 'Tidak ada notifikasi stok kritis' }}"
                            >
                                @if ($criticalProductsCount > 0)
                                    <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#ba1a1a] px-1 text-[10px] font-bold leading-none text-white">
                                        {{ $criticalProductsCount > 99 ? '99+' : $criticalProductsCount }}
                                    </span>
                                @endif
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.5 17.5h5m-2.5-2.5v5M12 4.75a6 6 0 0 1 6 6v1.37c0 .5.17.98.49 1.37l1 1.2a1 1 0 0 1-.77 1.64H5.28a1 1 0 0 1-.77-1.64l1-1.2c.32-.39.49-.87.49-1.37V10.75a6 6 0 0 1 6-6Zm0 14.5a2.75 2.75 0 0 1-2.58-1.75h5.16A2.75 2.75 0 0 1 12 19.25Z" />
                                </svg>
                            </summary>
                            <div class="absolute right-0 top-[calc(100%+0.75rem)] z-40 w-[320px] overflow-hidden rounded-2xl border border-[#c0c8cb] bg-white shadow-xl">
                                <div class="border-b border-slate-200 px-4 py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-slate-900">Notifikasi Stok</p>
                                            <p class="mt-1 text-xs text-slate-500">
                                                {{ $criticalProductsCount > 0 ? $criticalProductsCount . ' produk perlu perhatian' : 'Tidak ada stok kritis saat ini' }}
                                            </p>
                                        </div>
                                        <a href="{{ route('stocks.notifications') }}" class="text-xs font-semibold text-[#003441] transition hover:text-[#0f4c5c]">
                                            Lihat semua
                                        </a>
                                    </div>
                                </div>
                                @if ($criticalProductsPreview->isNotEmpty())
                                    <div class="max-h-80 overflow-y-auto py-2">
                                        @foreach ($criticalProductsPreview as $criticalProduct)
                                            <a href="{{ route('stocks.notifications') }}" class="flex items-start gap-3 px-4 py-3 transition hover:bg-[#f9f9fa]">
                                                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#ffdad6] text-[#ba1a1a]">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8.25v4.5m0 3h.01M10.04 4.72 3.56 15.53A1.5 1.5 0 0 0 4.85 17.8h14.3a1.5 1.5 0 0 0 1.29-2.27L13.96 4.72a1.5 1.5 0 0 0-2.92 0Z" />
                                                    </svg>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $criticalProduct->nama_produk }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">
                                                        Sisa {{ $criticalProduct->stok }} {{ $criticalProduct->satuan }} dari minimum {{ $criticalProduct->stok_minimum }} {{ $criticalProduct->satuan }}
                                                    </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="px-4 py-5 text-sm text-slate-500">
                                        Semua stok masih aman.
                                    </div>
                                @endif
                            </div>
                        </details>
                        <div class="hidden h-8 w-px bg-[#d8dee2] sm:block"></div>
                        <div class="flex items-center gap-3 rounded-full px-2 py-1 transition hover:bg-[#f3f4f5]">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-semibold text-slate-800">{{ $user?->name ?? 'Owner Admin' }}</p>
                                <p class="text-xs text-slate-500">{{ $user?->store_name ?? 'Sitori Workspace' }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#d0e1fb] text-sm font-bold text-[#003441] ring-4 ring-white">
                                {{ strtoupper(substr($user?->name ?? 'OW', 0, 2)) }}
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-5 sm:p-8">
                <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900">{{ $pageTitle }}</h1>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">{{ $pageSubtitle }}</p>
                    </div>
                    @hasSection('page_actions')
                        <div class="flex flex-wrap items-center gap-3">
                            @yield('page_actions')
                        </div>
                    @endif
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-sidebar-root]');
            if (!root) return;

            const overlay = root.querySelector('[data-sidebar-overlay]');
            const toggles = root.querySelectorAll('[data-sidebar-toggle]');
            const desktopQuery = window.matchMedia('(min-width: 768px)');
            const storageKey = 'sitori-sidebar-desktop-state';

            const syncSidebarState = () => {
                if (desktopQuery.matches) {
                    root.dataset.sidebarState = localStorage.getItem(storageKey) === 'collapsed' ? 'collapsed' : 'open';
                    return;
                }

                root.dataset.sidebarState = 'closed';
            };

            const persistDesktopState = () => {
                if (desktopQuery.matches) {
                    localStorage.setItem(storageKey, root.dataset.sidebarState === 'collapsed' ? 'collapsed' : 'open');
                }
            };

            const toggleSidebar = () => {
                const state = root.dataset.sidebarState;

                if (desktopQuery.matches) {
                    root.dataset.sidebarState = state === 'collapsed' ? 'open' : 'collapsed';
                    persistDesktopState();
                    return;
                }

                root.dataset.sidebarState = state === 'open' ? 'closed' : 'open';
            };

            syncSidebarState();
            desktopQuery.addEventListener('change', syncSidebarState);

            toggles.forEach((toggle) => {
                toggle.addEventListener('click', toggleSidebar);
            });

            overlay?.addEventListener('click', () => {
                if (!desktopQuery.matches) {
                    root.dataset.sidebarState = 'closed';
                }
            });
        })();
    </script>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);

                if (!input) {
                    return;
                }

                const isHidden = input.type === 'password';

                input.type = isHidden ? 'text' : 'password';
                button.setAttribute('aria-label', isHidden ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                button.querySelector('[data-eye-open]')?.classList.toggle('hidden', !isHidden);
                button.querySelector('[data-eye-closed]')?.classList.toggle('hidden', isHidden);
            });
        });
    </script>
</body>
</html>
