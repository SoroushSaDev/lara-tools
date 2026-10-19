/**
 * Light/dark/system theme handling.
 *
 * The initial class is applied by an inline <head> script before first paint;
 * this module only reacts to user interaction and OS preference changes.
 * The sidebar shows the current preference title next to its icon.
 */

import { onReady } from '../support/ready';

const STORAGE_KEY = 'lara-tools.theme';

/** Resolve whether dark styling should currently be applied. */
function prefersDark(pref) {
    if (pref === 'dark') return true;
    if (pref === 'light') return false;

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function currentPref() {
    return (
        localStorage.getItem(STORAGE_KEY)
        || document.documentElement.dataset.themePref
        || 'dark'
    );
}

/** Reflect the active preference in every sidebar/badge label. */
function applyTheme(pref) {
    document.documentElement.classList.toggle('dark', prefersDark(pref));

    document.querySelectorAll('[data-theme-toggle] i')
        .forEach((icon) => icon.classList.toggle('bi-sun-fill', !prefersDark(pref)));
}

/** Sync the theme dropdown <select> with the current preference. */
function syncDropdown() {
    document.querySelectorAll('select[name="appearance"][data-dropdown]').forEach((select) => {
        if (select.value !== currentPref()) {
            select.value = currentPref();
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}

// Follow live OS changes while in "system" mode.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (currentPref() === 'system') {
        applyTheme('system');
        syncDropdown();
    }
});

applyTheme(currentPref());
syncDropdown();

// Toggle button flips between light and dark explicitly (legacy support).
document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) {
        return;
    }

    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next);
    syncDropdown();
});

// Keep the stored value in sync when settings are saved server-side.
document.addEventListener('change', (event) => {
    if (event.target.name === 'appearance') {
        localStorage.setItem(STORAGE_KEY, event.target.value);
        applyTheme(event.target.value);
    }
});

onReady(syncDropdown);
