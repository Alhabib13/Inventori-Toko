<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judulHalaman ?? 'Masuk' }} - Sitori</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f8fa] font-sans text-slate-900 antialiased">
    @php
        $feedbackToasts = collect([
            ['type' => 'success', 'message' => session('success')],
            ['type' => 'success', 'message' => session('status')],
            ['type' => 'error', 'message' => session('error')],
            ['type' => 'error', 'message' => $errors->any() ? $errors->first() : null],
        ])->filter(fn ($toast) => filled($toast['message']))->values();
    @endphp

    <div class="pointer-events-none fixed inset-x-0 top-0 z-[70] h-1.5 origin-left scale-x-0 bg-gradient-to-r from-[#0f4c5c] via-[#2f7c92] to-[#8ac7d8] transition-transform duration-300" data-page-loader></div>

    @if ($feedbackToasts->isNotEmpty())
        <div class="pointer-events-none fixed right-4 top-4 z-[80] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3" data-toast-stack>
            @foreach ($feedbackToasts as $toast)
                <div class="pointer-events-auto overflow-hidden rounded-2xl border {{ $toast['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800' }} shadow-lg" data-toast>
                    <div class="flex items-start gap-3 px-4 py-3">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $toast['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            @if ($toast['type'] === 'success')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="m5 13 4 4L19 7" />
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold">{{ $toast['type'] === 'success' ? 'Berhasil' : 'Perlu Diperhatikan' }}</p>
                            <p class="mt-1 text-sm leading-6">{{ $toast['message'] }}</p>
                        </div>
                        <button type="button" class="rounded-full p-1 text-current/70 transition hover:bg-black/5 hover:text-current" data-toast-close aria-label="Tutup notifikasi">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 6 12 12M18 6 6 18" />
                            </svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <dialog class="w-[min(28rem,calc(100vw-2rem))] rounded-3xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-950/45" data-confirm-dialog>
        <div class="px-6 py-5">
            <div class="flex items-start gap-4">
                <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M10.04 4.72 3.56 15.53A1.5 1.5 0 0 0 4.85 17.8h14.3a1.5 1.5 0 0 0 1.29-2.27L13.96 4.72a1.5 1.5 0 0 0-2.92 0Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900" data-confirm-title>Konfirmasi Aksi</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600" data-confirm-message>Apakah kamu yakin ingin melanjutkan aksi ini?</p>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-confirm-cancel>
                    Batal
                </button>
                <button type="button" class="inline-flex h-11 items-center justify-center rounded-xl bg-[#003441] px-4 text-sm font-semibold text-white transition hover:bg-[#0f4c5c]" data-confirm-accept>
                    Lanjutkan
                </button>
            </div>
        </div>
    </dialog>

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
        (() => {
            const loader = document.querySelector('[data-page-loader]');
            const confirmDialog = document.querySelector('[data-confirm-dialog]');
            const confirmTitle = confirmDialog?.querySelector('[data-confirm-title]');
            const confirmMessage = confirmDialog?.querySelector('[data-confirm-message]');
            const confirmAccept = confirmDialog?.querySelector('[data-confirm-accept]');
            const confirmCancel = confirmDialog?.querySelector('[data-confirm-cancel]');
            let pendingForm = null;

            const showLoader = () => {
                if (!loader) return;
                loader.classList.remove('scale-x-0');
                loader.classList.add('scale-x-100');
            };

            window.addEventListener('pageshow', () => {
                if (!loader) return;
                loader.classList.add('scale-x-0');
                loader.classList.remove('scale-x-100');
            });

            document.querySelectorAll('[data-toast]').forEach((toast) => {
                const removeToast = () => {
                    toast.classList.add('opacity-0', 'translate-y-[-6px]');
                    setTimeout(() => toast.remove(), 180);
                };

                setTimeout(removeToast, 3600);
                toast.querySelector('[data-toast-close]')?.addEventListener('click', removeToast);
            });

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || link.target === '_blank' || link.hasAttribute('download')) return;
                showLoader();
            });

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)) return;

                if (form.dataset.confirm && !form.dataset.confirmApproved) {
                    event.preventDefault();
                    pendingForm = form;
                    if (confirmTitle) confirmTitle.textContent = form.dataset.confirmTitle || 'Konfirmasi Aksi';
                    if (confirmMessage) confirmMessage.textContent = form.dataset.confirm;
                    confirmDialog?.showModal();
                    return;
                }

                showLoader();

                if (form.dataset.loadingApplied === 'true') return;
                form.dataset.loadingApplied = 'true';

                const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
                const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');

                submitButtons.forEach((button) => {
                    button.disabled = true;
                    if (button instanceof HTMLButtonElement) {
                        button.dataset.originalHtml = button.innerHTML;
                    } else {
                        button.dataset.originalValue = button.value;
                    }
                    button.classList.add('cursor-not-allowed', 'opacity-80');
                });

                if (submitter instanceof HTMLButtonElement) {
                    const loadingText = submitter.dataset.loadingText || 'Memproses...';
                    submitter.innerHTML = `<span class="inline-flex items-center gap-2"><svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path></svg><span>${loadingText}</span></span>`;
                } else if (submitter instanceof HTMLInputElement) {
                    submitter.value = submitter.dataset.loadingText || 'Memproses...';
                }
            });

            confirmAccept?.addEventListener('click', () => {
                if (!pendingForm) return;
                pendingForm.dataset.confirmApproved = 'true';
                confirmDialog?.close();
                pendingForm.requestSubmit();
                pendingForm = null;
            });

            confirmCancel?.addEventListener('click', () => {
                confirmDialog?.close();
                pendingForm = null;
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
