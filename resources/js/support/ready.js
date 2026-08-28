/**
 * Initialisation bridge for Turbo-powered navigation.
 *
 * Turbo swaps the <body> on every link click without reloading the page,
 * so DOMContentLoaded never fires again. Feature modules register their
 * setup here: it runs once on first paint and again after every Turbo
 * visit (each visit renders a fresh DOM, making re-runs safe).
 */

const initialisers = [];

/** Run every registered initialiser. */
function runAll() {
    initialisers.forEach((init) => init());
}

// First paint (module scripts execute before DOMContentLoaded completes,
// hence the readyState check) ...
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runAll, { once: true });
} else {
    runAll();
}

// ... and every subsequent Turbo navigation.
document.addEventListener('turbo:load', runAll);

/**
 * Register a feature initialiser.
 *
 * @param {Function} init
 */
export function onReady(init) {
    initialisers.push(init);
}
