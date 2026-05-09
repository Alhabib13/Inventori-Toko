<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judulHalaman ?? 'Masuk' }} - Sitori</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8fa] font-sans text-slate-900 antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="@yield('auth_container_class', 'w-full max-w-[28rem]')">
            <div class="mb-8 flex flex-col items-center text-center">
                <h1 class="font-extrabold tracking-tight text-[#123b4a]" style="font-size: clamp(3.6rem, 7vw, 4.75rem); line-height: 1;">
                    Sitori
                </h1>
                @hasSection('auth_heading')
                    <p class="mt-2 text-sm font-medium text-slate-500">@yield('auth_heading')</p>
                @endif
            </div>

            @yield('content')

            <footer class="mt-6 text-center text-xs text-slate-400">
                <p>&copy; {{ date('Y') }} Sitori</p>
            </footer>
        </div>
    </main>
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
