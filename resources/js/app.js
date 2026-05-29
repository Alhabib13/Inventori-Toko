import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-upload-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const input = form.querySelector('[data-upload-input]');
        const trigger = form.querySelector('[data-upload-trigger]');

        if (!(input instanceof HTMLInputElement) || !(trigger instanceof HTMLButtonElement)) {
            return;
        }

        const setLoadingState = () => {
            trigger.disabled = true;
            trigger.classList.add('cursor-not-allowed', 'opacity-80');
            trigger.innerHTML = `<span class="inline-flex items-center gap-2"><svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" class="opacity-25" stroke="currentColor" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" class="opacity-90" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path></svg><span>${trigger.dataset.loadingText || 'Mengunggah file...'}</span></span>`;
        };

        trigger.addEventListener('click', () => {
            input.click();
        });

        input.addEventListener('change', () => {
            if (!input.files || input.files.length === 0) {
                return;
            }

            setLoadingState();
            form.requestSubmit();
        });
    });
});
