/**
 * Generic tab switcher used by the Clock and Settings pages.
 *
 * Contract with Blade views:
 *  - Tab triggers:  [data-tab="panel-id"]
 *  - Panels:        [data-tab-panel="panel-id"]
 *  - Active styling is applied to triggers via [data-tab].is-active;
 *      panels are toggled by the hidden attribute, so exactly one is open.
 *  - An initial panel can be preselected server-side by rendering that
 *      trigger/panel pair active and all others hidden.
 */

import { onReady } from '../support/ready';

function activate(root, id) {
    root.setAttribute('role', root.getAttribute('role') ?? 'presentation');

    root.querySelectorAll('[data-tab]').forEach((trigger) => {
        const isActive = trigger.dataset.tab === id;

        trigger.setAttribute('role', 'tab');
        trigger.setAttribute('aria-selected', String(isActive));
        trigger.classList.toggle('is-active', isActive);
    });

    root.querySelectorAll('[data-tab-panel]').forEach((panel) => {
        panel.setAttribute('role', 'tabpanel');
        panel.toggleAttribute('hidden', panel.dataset.tabPanel !== id);
    });
}

onReady(() => {
    const roots = document.querySelectorAll('[data-tabs]');

    roots.forEach((root) => {
        // Preselect from ?tab= when present, otherwise whatever the view
        // rendered as active (first tab by default).
        const requested = new URLSearchParams(window.location.search).get('tab');
        const known = [...root.querySelectorAll('[data-tab]')].some((t) => t.dataset.tab === requested);

        activate(root, known && requested ? requested : (root.querySelector('[data-tab].is-active')?.dataset.tab
            || root.querySelector('[data-tab]')?.dataset.tab));

        if (!root.dataset.tabsBound) {
            root.dataset.tabsBound = '1';

            root.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-tab]');

                if (trigger) {
                    activate(root, trigger.dataset.tab);
                }
            });
        }
    });
});
