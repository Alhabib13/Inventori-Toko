<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judulHalaman ?? 'Masuk' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f7fb] text-slate-900 antialiased">
    <main class="relative min-h-screen overflow-hidden px-4 py-8 sm:px-6 lg:px-10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(17,55,101,0.14),_transparent_40%),linear-gradient(180deg,_#f9fbff_0%,_#eef3f8_100%)]"></div>
        <div class="relative mx-auto flex min-h-[calc(100vh-4rem)] max-w-7xl flex-col">
            <header class="flex items-center justify-between px-2 py-2 sm:px-3">
                <a href="{{ route('login') }}" class="text-[1.75rem] font-bold tracking-[-0.04em] text-[#163760]">Sitori</a>
                <span class="text-xs font-bold uppercase tracking-[0.24em] text-[#163760]">{{ $authTab ?? 'MASUK' }}</span>
            </header>

            <section class="my-auto grid overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_32px_80px_rgba(15,23,42,0.10)] lg:grid-cols-[1.02fr_1.4fr]">
                <aside class="relative overflow-hidden bg-[#163760] px-8 py-10 text-white sm:px-10 sm:py-12 lg:min-h-[35rem]">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,_rgba(255,255,255,0.10),_transparent_35%),linear-gradient(160deg,_rgba(255,255,255,0.04),_rgba(255,255,255,0))]"></div>
                    <div class="absolute inset-x-0 bottom-0 h-44 bg-[linear-gradient(180deg,_rgba(6,22,42,0),_rgba(6,22,42,0.2)),radial-gradient(circle_at_bottom_right,_rgba(112,170,255,0.35),_transparent_38%)]"></div>

                    <div class="relative flex h-full flex-col justify-between gap-10">
                        <div class="space-y-6">
                            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl border border-white/15 bg-white/10 backdrop-blur-sm">
                                <svg class="h-7 w-7 text-white" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M6.75 7.25A2.25 2.25 0 0 1 9 5h6a2.25 2.25 0 0 1 2.25 2.25v9.5A2.25 2.25 0 0 1 15 19H9a2.25 2.25 0 0 1-2.25-2.25v-9.5Z" fill="currentColor" opacity=".18"/>
                                    <path d="M7.5 8A1.5 1.5 0 0 1 9 6.5h6A1.5 1.5 0 0 1 16.5 8v8A1.5 1.5 0 0 1 15 17.5H9A1.5 1.5 0 0 1 7.5 16V8Z" stroke="currentColor" stroke-width="1.5"/>
                                    <path d="M9 9.5h6M10.5 13h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>

                            <div class="max-w-sm space-y-4">
                                <h1 class="text-4xl font-bold leading-none tracking-[-0.06em] sm:text-5xl">@yield('auth_headline')</h1>
                                <p class="max-w-xs text-sm leading-7 text-slate-300 sm:text-base">
                                    @yield('auth_copy')
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4">
                            @yield('auth_features')
                        </div>
                    </div>
                </aside>

                <div class="bg-white px-6 py-8 sm:px-10 sm:py-12 lg:px-16 lg:py-14">
                    @yield('content')
                </div>
            </section>

            <footer class="flex flex-col gap-4 px-2 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-3">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                    <span class="font-bold text-[#163760]">Sitori</span>
                    <span>&copy; 2026 Sitori. Ruang kerja inventori untuk pemilik toko.</span>
                </div>

                <div class="flex flex-wrap items-center gap-5">
                    <a href="#" class="transition hover:text-[#163760]">Syarat Layanan</a>
                    <a href="#" class="transition hover:text-[#163760]">Kebijakan Privasi</a>
                    <a href="#" class="transition hover:text-[#163760]">Pusat Bantuan</a>
                </div>
            </footer>
        </div>
    </main>
</body>
</html>