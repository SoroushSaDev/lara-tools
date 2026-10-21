/**
 * Search boxes: filter as you type, plus the global search overlay.
 *
 * Contract with Blade views:
 *  - [data-search-form] is a GET form carrying data-turbo-frame="<results-id>",
 *    so a reload swaps only the results list. Its [data-search-input] must sit
 *    outside that frame — that is what preserves focus and the caret.
 *  - [data-search-clear] empties the term and reloads the list at once.
 *  - [data-search-open] buttons, Ctrl/Cmd+K and the [data-search-backdrop]
 *    scrim drive the overlay in layouts/partial/search-overlay.blade.php.
 *
 * The controller owns the matching; this module only decides *when* to ask it.
 */

import { onReady } from '../support/ready';

// Long enough that a fast typist triggers one request per word, short
// enough that the list still feels like it reacts to the keys.
const DEBOUNCE_MS = 220;

/** A term the server would treat as the same query. */
function term(value) {
    return value.trim().toLowerCase();
}

function inputOf(root) {
    return root?.querySelector('[data-search-input]') ?? null;
}

function clearOf(root) {
    return root?.querySelector('[data-search-clear]') ?? null;
}

/** The × only makes sense while there is something to undo. */
function syncClearButton(form) {
    clearOf(form)?.classList.toggle('invisible', term(inputOf(form)?.value) === '');
}

/**
 * Keep the address bar honest after a same-page filter, so a refresh or a
 * shared link lands on the filtered list. Forms pointing at another route
 * (the overlay on any page, /search) leave the URL alone.
 */
function syncUrl(form) {
    const target = new URL(form.action);

    if (target.pathname !== window.location.pathname) return;

    const value = inputOf(form)?.value.trim() ?? '';

    if (value === '') {
        target.search = '';
    } else {
        target.searchParams.set('q', value);
    }

    // history.state holds Turbo's own snapshot reference, so reuse it.
    history.replaceState(history.state, '', `${target.pathname}${target.search}`);
}

function bindForm(form) {
    const input = inputOf(form);

    if (!input) return;

    let timer = null;
    let last = term(input.value);

    /** Ask the server for the current term and remember it as answered. */
    const send = () => {
        last = term(input.value);
        syncClearButton(form);
        form.requestSubmit();
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        syncClearButton(form);

        timer = setTimeout(() => {
            timer = null;

            if (term(input.value) !== last) send();
        }, DEBOUNCE_MS);
    });

    // Enter submits before the debounce lands; skip the queued reload.
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            clearTimeout(timer);
            timer = null;
            last = term(input.value);
            syncClearButton(form);

            return;
        }

        if (event.key !== 'Escape') return;

        // Own the key so Chromium's native search-field clear cannot desync
        // the list from the box.
        event.preventDefault();

        // With nothing to undo, let Escape reach the overlay and close it.
        if (term(input.value) === '') return;

        event.stopPropagation();

        clearTimeout(timer);
        timer = null;
        input.value = '';
        send();
    });

    clearOf(form)?.addEventListener('click', () => {
        clearTimeout(timer);
        timer = null;
        input.value = '';
        send();
        input.focus();
    });

    form.addEventListener('turbo:submit-end', (event) => {
        if (event.detail.success) syncUrl(form);
    });
}

const overlay = () => document.querySelector('[data-search-overlay]');

let opener = null;

function openOverlay(trigger) {
    const box = overlay();

    if (!box || !box.classList.contains('hidden')) return;

    opener = trigger ?? document.activeElement;

    // Tailwind's .hidden outranks .flex, so both classes are needed.
    box.classList.remove('hidden');
    box.classList.add('flex');

    const field = inputOf(box);
    field?.focus();

    // A carried-over term is ready to be replaced in one keystroke.
    field?.select();
}

function closeOverlay() {
    const box = overlay();

    if (!box || box.classList.contains('hidden')) return;

    box.classList.remove('flex');
    box.classList.add('hidden');

    opener?.focus();
    opener = null;
}

// Page-level listeners: bound once, they survive every Turbo visit.
document.addEventListener('keydown', (event) => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
        event.preventDefault();

        const box = overlay();

        if (!box) {
            // Pages without the overlay (/search) still get the shortcut.
            inputOf(document.querySelector('[data-search-form]'))?.focus();

            return;
        }

        if (box.classList.contains('hidden')) {
            openOverlay();
        } else {
            closeOverlay();
        }

        return;
    }

    if (event.key === 'Escape') {
        const box = overlay();

        if (box && !box.classList.contains('hidden')) {
            closeOverlay();
        }
    }
});

onReady(() => {
    document.querySelectorAll('[data-search-form]').forEach(bindForm);
    document.querySelectorAll('[data-search-open]').forEach((button) => {
        button.addEventListener('click', () => openOverlay(button));
    });

    overlay()?.querySelector('[data-search-backdrop]')
        ?.addEventListener('click', closeOverlay);
});
