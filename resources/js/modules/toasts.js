/**
 * Toast notifications: auto-dismiss after a delay with a smooth exit.
 * Runs after every Turbo navigation since each page may render new toasts.
 */

import { onReady } from '../support/ready';

const AUTO_DISMISS_AFTER = 4500;

function dismiss(toast) {
    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(24px)';

    setTimeout(() => toast.remove(), 320);
}

onReady(() => {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        setTimeout(() => dismiss(toast), AUTO_DISMISS_AFTER);
    });
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-toast-dismiss]');

    if (button) {
        dismiss(button.closest('[data-toast]'));
    }
});
