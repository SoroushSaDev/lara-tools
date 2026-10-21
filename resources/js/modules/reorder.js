/**
 * Draggable reorder lists (Settings › Dashboard).
 *
 * Contract with Blade views:
 *  - Root:      [data-reorder] wraps one list and the input that submits it
 *  - Value:     input[data-reorder-value] holds a JSON array of tile keys
 *  - Rows:      .drag-row[data-key] inside the root, draggable="true"
 *  - Buttons:   [data-move="up|down"] inside a row
 *
 * Dragging begins on a row but never on a control inside it, so each row's
 * toggle and arrow buttons keep working. Arrows are also the touch and
 * keyboard path, since HTML5 drag events do not fire for those.
 *
 * Every change rewrites the JSON input; the server normalises it against the
 * tab's canonical key list, so a hand-edited payload cannot hide a tile.
 */

import { onReady } from '../support/ready';

const rowsIn = (root) => [...root.querySelectorAll('.drag-row')];

function publish(root) {
    const input = root.querySelector('[data-reorder-value]');

    if (input) input.value = JSON.stringify(rowsIn(root).map((row) => row.dataset.key));
}

/** Move a row one step, focusing nothing so repeat presses stay natural. */
function move(root, row, direction) {
    const rows = rowsIn(root);
    const index = rows.indexOf(row);
    const target = direction === 'up' ? rows[index - 1] : rows[index + 1];

    if (!target) return;

    const list = row.parentElement;

    list.insertBefore(direction === 'up' ? row : target, direction === 'up' ? target : row.nextSibling);
    publish(root);
}

function bind(root) {
    if (root.dataset.reorderBound) return;

    root.dataset.reorderBound = '1';

    let dragged = null;

    root.addEventListener('dragstart', (event) => {
        const row = event.target.closest('.drag-row');

        // A drag begun on the toggle or an arrow is really a click in progress.
        if (!row || event.target.closest('input, button, label')) {
            event.preventDefault();

            return;
        }

        dragged = row;
        row.dataset.dragging = 'true';

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', row.dataset.key);
        }
    });

    root.addEventListener('dragover', (event) => {
        if (!dragged) return;

        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';

        const row = event.target.closest('.drag-row');

        if (!row || row === dragged || row.parentElement !== dragged.parentElement) return;

        const box = row.getBoundingClientRect();

        row.parentElement.insertBefore(dragged, event.clientY > box.top + box.height / 2 ? row.nextSibling : row);
    });

    root.addEventListener('drop', (event) => {
        if (dragged) event.preventDefault();
    });

    root.addEventListener('dragend', () => {
        if (!dragged) return;

        delete dragged.dataset.dragging;
        dragged = null;

        publish(root);
    });

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-move]');

        if (button) move(root, button.closest('.drag-row'), button.dataset.move);
    });

    // Keep the submitted value in sync even when the user never drags.
    publish(root);
}

onReady(() => {
    document.querySelectorAll('[data-reorder]').forEach(bind);
});
