/**
 * Light/dark/system theme handling.
 *
 * The initial class is applied by an inline <head> script before first paint;
 * this module only reacts to user interaction and OS preference changes.
 * The sidebar shows the current preference title next to its icon.
 */

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
function updateLabels() {
    const pref = currentPref();

    document.querySelectorAll('[data-theme-label]').forEach((label) => {
        label.textContent = pref.charAt(0).toUpperCase() + pref.slice(1);
    });
}

function applyTheme(pref) {
    document.documentElement.classList.toggle('dark', prefersDark(pref));
    updateLabels();

    document.querySelectorAll('[data-theme-toggle] i')
        .forEach((icon) => icon.classList.toggle('bi-sun-fill', !prefersDark(pref)));
}

// Follow live OS changes while in "system" mode.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (currentPref() === 'system') {
        applyTheme('system');
    }
});

applyTheme(currentPref());

// Toggle button flips between light and dark explicitly.
document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-theme-toggle]')) {
        return;
    }

    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next);
});

// Keep the stored value in sync when settings are saved server-side.
document.addEventListener('change', (event) => {
    if (event.target.name === 'appearance') {
        localStorage.setItem(STORAGE_KEY, event.target.value);
    }
});
