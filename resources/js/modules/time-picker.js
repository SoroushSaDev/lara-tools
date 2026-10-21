/**
 * Reusable time-of-day picker (resources/views/components/time-picker.blade.php).
 *
 * Contract:
 *  - Root:    [data-time-picker][data-format="24h|12h"], role="group"
 *  - Value:   input[data-time-value] inside the root — the only thing consumers
 *             read. It holds "HH:MM" in 24-hour form, or '' when incomplete.
 *  - Parts:   select[data-time-part="hour|minute|meridiem"], each [data-dropdown]
 *             so dropdown.js paints it. The meridiem select only exists in 12h.
 *  - Writing input.value from the outside re-paints the parts on the next
 *    'change' event bubbling from the hidden input.
 *
 * Hour/minute start on a disabled "00" placeholder that carries no value: an
 * unfinished pick simply keeps the value empty, so a half-chosen time can never
 * become a wrong alarm time.
 */

import { onReady } from '../support/ready';

const pad = (value) => String(value).padStart(2, '0');

/** Split a stored "HH:MM" into part-select values for the picker's format. */
function toParts(value, is24) {
    const match = /^(\d{1,2}):(\d{2})$/.exec(value);

    if (!match) return null;

    const hour = Number(match[1]);
    const minute = Number(match[2]);

    return {
        hour: is24 ? hour : (hour % 12) || 12,
        minute,
        meridiem: hour >= 12 ? 'pm' : 'am',
    };
}

/** Fold the parts back into a 24-hour value; a missing pick means no time yet. */
function toValue(selects, is24) {
    const hour = selects.hour.value;
    const minute = selects.minute.value;

    if (hour === '' || minute === '') return '';

    if (is24) return `${pad(Number(hour))}:${pad(Number(minute))}`;

    const base = Number(hour) % 12;

    return `${pad(base + (selects.meridiem?.value === 'pm' ? 12 : 0))}:${pad(Number(minute))}`;
}

function bind(root) {
    if (root.dataset.bound) return;

    const hidden = root.querySelector('[data-time-value]');

    if (!hidden) return;

    root.dataset.bound = '1';

    const is24 = root.dataset.format !== '12h';
    const rendered = hidden.value;
    const selects = {
        hour: root.querySelector('[data-time-part="hour"]'),
        minute: root.querySelector('[data-time-part="minute"]'),
        meridiem: root.querySelector('[data-time-part="meridiem"]'),
    };

    /** Paint the part selects from the stored value and refresh their labels. */
    const sync = () => {
        const parts = toParts(hidden.value, is24);

        ['hour', 'minute', 'meridiem'].forEach((name) => {
            const select = selects[name];

            if (!select) return;

            const next = name === 'meridiem' ? parts?.meridiem ?? 'am' : parts?.[name] ?? '';

            select.value = next;

            // dropdown.js repaints its trigger on 'change' — and a reset leaves
            // the select at its default without firing anything, so the event
            // is dispatched even when the value itself did not move.
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };

    root.addEventListener('change', (event) => {
        if (!event.target.matches('[data-time-part]')) return;

        hidden.value = toValue(selects, is24);
    });

    // A host writing the value directly (a reset, or a preset) needs a repaint.
    hidden.addEventListener('change', sync);

    const form = root.closest('form');

    if (form) {
        // A scripted input.value write also dirties the value attribute here, so
        // the browser's own reset would restore the last pick rather than the
        // time the page was rendered with; `rendered` is that original value.
        form.addEventListener('reset', () => setTimeout(() => {
            hidden.value = rendered;
            sync();
        }, 0));
    }

    sync();
}

onReady(() => {
    document.querySelectorAll('[data-time-picker]').forEach(bind);
});
