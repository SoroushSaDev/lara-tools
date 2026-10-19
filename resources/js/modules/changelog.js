/**
 * Version-history modal on the About page.
 *
 * Contract with about/index.blade.php:
 *  - Trigger: [data-changelog-open]
 *  - Modal:   #changelog-modal, dismissed via [data-changelog-close] or Esc.
 */

import { onReady } from '../support/ready';

function setVisible(visible) {
    const modal = document.getElementById('changelog-modal');

    if (!modal) return;

    modal.classList.toggle('hidden', !visible);
    modal.classList.toggle('flex', visible);
}

onReady(() => {
    const trigger = document.querySelector('[data-changelog-open]');

    if (!trigger || trigger.dataset.bound) return;

    trigger.dataset.bound = '1';
    trigger.addEventListener('click', () => setVisible(true));

    document.querySelectorAll('[data-changelog-close]').forEach((el) => {
        el.addEventListener('click', () => setVisible(false));
    });
});

// Bound once at module scope; only acts while a changelog modal is open.
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setVisible(false);
});
