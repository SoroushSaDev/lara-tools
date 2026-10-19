/**
 * Confirmation dialog for destructive actions.
 *
 * Any element carrying a `data-confirm="message"` attribute triggers the
 * dialog; confirming submits its closest form (or follows its link).
 */

const dialog = () => document.getElementById('confirm-dialog');

let pendingAction = null;

function isOpen() {
    return !dialog().classList.contains('hidden');
}

function openDialog(message) {
    dialog().querySelector('#confirm-dialog-message').textContent = message;
    dialog().classList.remove('hidden');
    dialog().classList.add('flex');
}

function closeDialog() {
    dialog().classList.add('hidden');
    dialog().classList.remove('flex');
    pendingAction = null;
}

function runPendingAction() {
    const action = pendingAction;

    closeDialog();

    if (!action) return;

    const form = action.closest('form');

    if (form) {
        // requestSubmit() keeps validation and the submit event alive, so
        // Turbo (and any submit handlers) still see the navigation.
        form.requestSubmit();
    } else if (action.href) {
        window.location.href = action.href;
    }
}

// Capture phase so we can intercept submits before they happen.
document.addEventListener('click', (event) => {
    if (event.target.closest('[data-confirm-cancel]')) {
        closeDialog();

        return;
    }

    if (event.target.closest('#confirm-dialog-accept')) {
        runPendingAction();

        return;
    }

    // While the dialog is open, swallow clicks elsewhere.
    if (isOpen()) return;

    const trigger = event.target.closest('[data-confirm]');

    if (!trigger) return;

    event.preventDefault();
    event.stopPropagation();
    pendingAction = trigger;
    openDialog(trigger.dataset.confirm);
}, true);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && isOpen()) {
        closeDialog();
    }
});
