<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judulHalaman ?? 'Sistem Manajemen Inventori' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="flex min-h-screen">
        <aside class="w-72 bg-slate-900 px-6 py-8 text-white">
            <div class="mb-8">
                <h1 class="text-xl font-bold">Inventori Toko</h1>
                <p class="mt-2 text-sm text-slate-300">Skeleton Laravel 12</p>
            </div>

            <nav class="space-y-2 text-sm">
                <a href="{{ route('dashboard.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Dashboard</a>
                <a href="{{ route('products.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Produk</a>
                <a href="{{ route('stocks.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Stok</a>
                <a href="{{ route('transactions.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Penjualan</a>
                <a href="{{ route('purchases.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Pembelian</a>
                <a href="{{ route('suppliers.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Supplier</a>
                <a href="{{ route('reports.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Laporan</a>
                <a href="{{ route('forecasts.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Prediksi</a>
                <a href="{{ route('users.index') }}" class="block rounded-lg px-3 py-2 hover:bg-slate-800">Pengguna</a>
            </nav>
        </aside>

        <div class="flex-1">
            <header class="border-b border-slate-200 bg-white px-6 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $judulHalaman ?? 'Dashboard' }}</h2>
                        <p class="text-sm text-slate-500">Kerangka modul aplikasi inventori toko/warung.</p>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
