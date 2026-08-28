/**
 * Todo list interactions: optimistic item toggling via PATCH requests.
 *
 * Contract with the Blade view:
 *  - Page root:  [data-todo-page]
 *  - Checkboxes: input[data-toggle-url] (one per checklist item)
 *  - Progress:   [data-progress-bar] (width %) and [data-progress-text]
 */

import { onReady } from '../support/ready';

onReady(() => {
    const page = document.querySelector('[data-todo-page]');

    if (!page) return;

    const progressBar = page.querySelector('[data-progress-bar]');
    const progressText = page.querySelector('[data-progress-text]');

    function updateProgress({ completed, total, percent }) {
        if (progressBar) progressBar.style.width = `${percent}%`;
        if (progressText) progressText.textContent = `${completed}/${total} done`;
    }

    function setItemState(input, completed) {
        const label = input.closest('.todo-item');

        if (!label) return;

        input.checked = completed;

        label.querySelector('[data-item-text]').classList.toggle('todo-item-done', completed);
    }

    document.querySelectorAll('input[data-toggle-url]').forEach((input) => {
        input.addEventListener('change', async () => {
            // Optimistically flip the UI; roll back if the request fails.
            const nowCompleted = input.checked;

            setItemState(input, nowCompleted);

            try {
                const response = await window.axios.patch(input.dataset.toggleUrl);

                updateProgress(response.data.progress);
            } catch {
                setItemState(input, !nowCompleted);
            }
        });
    });
});
