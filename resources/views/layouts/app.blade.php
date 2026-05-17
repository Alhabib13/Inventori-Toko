<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judulHalaman ?? 'Sitori' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f9f9fa] font-sans text-[#191c1d] antialiased">
    @php
        $user = auth()->user();
        $role = $user?->role;
        $modeApp = $user?->mode_app;

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
                    ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'reports'],
                    ['label' => 'Prediksi Stok', 'route' => 'forecasts.index', 'icon' => 'forecast'],
                    ['label' => 'Manajemen User', 'route' => 'users.index', 'icon' => 'users'],
                    ['label' => 'Register User', 'route' => 'users.register', 'icon' => 'user-add'],
                    ['label' => 'Produk', 'route' => 'products.index', 'icon' => 'products'],
                    ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'categories'],
                    ['label' => 'Stok', 'route' => 'stocks.role-home', 'icon' => 'stocks'],
                ],
            'gudang' => $modeApp === 'lengkap'
                ? [
                    ['label' => 'Stok', 'route' => 'stocks.role-home', 'icon' => 'stocks'],
                    ['label' => 'Produk', 'route' => 'products.index', 'icon' => 'products'],
                    ['label' => 'Kategori', 'route' => 'categories.index', 'icon' => 'categories'],
                    ['label' => 'Supplier', 'route' => 'suppliers.index', 'icon' => 'supplier'],
                    ['label' => 'Pembelian', 'route' => 'purchases.index', 'icon' => 'purchase'],
                    ['label' => 'Notifikasi Stok', 'route' => 'stocks.notifications', 'icon' => 'alert'],
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

    <div class="flex min-h-screen bg-[#f9f9fa]">
        <aside class="hidden w-64 shrink-0 border-r border-[#c0c8cb] bg-white md:flex md:flex-col">
            <div class="border-b border-[#c0c8cb] px-6 py-7">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#0f4c5c]/12 text-[#0f4c5c]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m4.75 7.75 7.25-3 7.25 3M4.75 7.75 12 11l7.25-3M4.75 7.75v8.5L12 19.5l7.25-3.25v-8.5M12 11v8.5" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-extrabold tracking-tight text-[#003441]">Sitori</p>
                        <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $modeApp === 'lengkap' ? 'Mode Lengkap' : 'Workspace Toko' }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @foreach ($sidebarLinks as $link)
                    @php
                        $isActive = request()->routeIs($link['route']) || ($link['route'] === 'stocks.role-home' && request()->routeIs('stocks.*'));
                    @endphp
                    <a
                        href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition {{ $isActive ? 'border-l-4 border-[#003441] bg-[#d0e1fb]/35 font-semibold text-[#003441]' : 'text-slate-600 hover:bg-[#f3f4f5] hover:text-slate-900' }}"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $iconMap[$link['icon']] ?? $iconMap['dashboard'] !!}
                        </svg>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto border-t border-[#c0c8cb] px-3 py-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.75 7.75V6.5A1.75 1.75 0 0 1 12.5 4.75h4A1.75 1.75 0 0 1 18.25 6.5v11A1.75 1.75 0 0 1 16.5 19.25h-4a1.75 1.75 0 0 1-1.75-1.75v-1.25M14 12H4.75m0 0 2.75-2.75M4.75 12l2.75 2.75" />
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-[#c0c8cb] bg-white/95 backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-8">
                    <div class="min-w-0 flex-1">
                        <div class="relative hidden max-w-md sm:block">
                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m21 21-4.35-4.35M10.75 18.5a7.75 7.75 0 1 1 0-15.5 7.75 7.75 0 0 1 0 15.5Z" />
                            </svg>
                            <input type="text" placeholder="Cari produk, kategori, supplier, atau user..." class="h-11 w-full rounded-full border border-[#c0c8cb] bg-[#f3f4f5] pl-10 pr-4 text-sm text-slate-700 outline-none transition focus:border-[#003441] focus:ring-2 focus:ring-[#003441]/10" />
                        </div>
                        <div class="sm:hidden">
                            <p class="text-xl font-extrabold tracking-tight text-[#003441]">Sitori</p>
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-slate-500">{{ $modeApp === 'lengkap' ? 'Mode Lengkap' : 'Workspace Toko' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" class="rounded-full p-2 text-slate-500 transition hover:bg-[#f3f4f5] hover:text-slate-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.5 17.5h5m-2.5-2.5v5M12 4.75a6 6 0 0 1 6 6v1.37c0 .5.17.98.49 1.37l1 1.2a1 1 0 0 1-.77 1.64H5.28a1 1 0 0 1-.77-1.64l1-1.2c.32-.39.49-.87.49-1.37V10.75a6 6 0 0 1 6-6Zm0 14.5a2.75 2.75 0 0 1-2.58-1.75h5.16A2.75 2.75 0 0 1 12 19.25Z" />
                            </svg>
                        </button>
                        <button type="button" class="rounded-full p-2 text-slate-500 transition hover:bg-[#f3f4f5] hover:text-slate-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.09 9a3 3 0 1 1 5.82 0c0 1.5-1 2.23-1.91 2.91-.76.56-1.41 1.05-1.41 1.84M12 17h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </button>
                        <div class="hidden h-8 w-px bg-[#c0c8cb] sm:block"></div>
                        <div class="flex items-center gap-3 rounded-xl px-2 py-1 transition hover:bg-[#f3f4f5]">
                            <div class="hidden text-right sm:block">
                                <p class="text-sm font-semibold text-slate-800">{{ $user?->name ?? 'Owner Admin' }}</p>
                                <p class="text-xs text-slate-500">{{ $user?->store_name ?? 'Sitori Workspace' }}</p>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#d0e1fb] text-sm font-bold text-[#003441]">
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
</body>
</html>
