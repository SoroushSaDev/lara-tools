/**
 * Todo list interactions: optimistic item toggling via PATCH requests.
 *
 * Contract with the Blade view:
 *  - Page root:  [data-todo-page]
 *  - Checkboxes: input[data-toggle-url] (one per checklist item)
 *  - Progress:   [data-progress-bar] (width %) and [data-progress-text]
 *
 * Listeners are attached at the document level (once) so Turbo's DOM
 * morphing can never bind the same handler to a checkbox twice.
 */

import { onReady } from '../support/ready';

let progressState = { bar: null, text: null };

onReady(() => {
    const page = document.querySelector('[data-todo-page]');

    progressState = {
        bar: page?.querySelector('[data-progress-bar]') ?? null,
        text: page?.querySelector('[data-progress-text]') ?? null,
    };
});

function setItemState(input, completed) {
    const label = input.closest('.todo-item');

    if (!label) return;

    input.checked = completed;

    label.querySelector('[data-item-text]').classList.toggle('todo-item-done', completed);
}

document.addEventListener('change', async (event) => {
    const input = event.target.closest('input[data-toggle-url]');

    if (!input) return;

    // Optimistically flip the UI; roll back if the request fails.
    const nowCompleted = input.checked;

    setItemState(input, nowCompleted);

    try {
        const response = await window.axios.patch(input.dataset.toggleUrl);
        const { completed, total, percent } = response.data.progress;

        if (progressState.bar) progressState.bar.style.width = `${percent}%`;
        if (progressState.text) progressState.text.textContent = `${completed}/${total} done`;
    } catch {
        setItemState(input, !nowCompleted);
    }
});
