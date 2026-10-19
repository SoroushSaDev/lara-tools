/**
 * Todo editor — dynamic checklist rows on the create/edit pages.
 *
 * Contract with todos/partial/form.blade.php:
 *  - Row list:     #todo-items
 *  - Row template: #item-template (a <template> with one li)
 *  - Add trigger:  #add-item
 *  - Remove:       [data-remove-item] inside each row
 */

import { onReady } from '../support/ready';

onReady(() => {
    const list = document.getElementById('todo-items');
    const template = document.getElementById('item-template');
    const addButton = document.getElementById('add-item');

    if (!list || !template || !addButton || list.dataset.bound) return;

    list.dataset.bound = '1';

    const addRow = (focus = true, value = '') => {
        const row = template.content.firstElementChild.cloneNode(true);
        const input = row.querySelector('input');

        input.value = value;
        list.appendChild(row);

        if (focus) input.focus();

        return row;
    };

    addButton.addEventListener('click', () => addRow());

    list.addEventListener('click', (event) => {
        if (!event.target.closest('[data-remove-item]')) return;

        // Keep at least one row on screen.
        if (list.children.length > 1) {
            event.target.closest('li').remove();
        }
    });

    // Enter adds the next row; Backspace on an empty row removes it.
    list.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && !(event.key === 'Backspace' && event.target.value === '')) return;

        event.preventDefault();

        if (event.key === 'Enter') {
            addRow(true);

            return;
        }

        if (list.children.length > 1) {
            const previous = event.target.closest('li').previousElementSibling;

            event.target.closest('li').remove();
            previous?.querySelector('input')?.focus();
        }
    });
});
