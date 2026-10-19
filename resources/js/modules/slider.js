/**
 * Reusable filled-track slider (resources/views/components/slider.blade.php).
 *
 * Contract:
 *  - Root:  .slider[data-slider] with --p (0%-100%) owned by this module
 *  - Value: input[type=range].slider-input inside the root (native, focusable)
 *  - Paint: .slider-rail > .slider-fill, .slider-thumb, output.slider-tip
 *  - The tip shows the formatted value while hovering, dragging or focused.
 *
 * Pointer drag, click-to-position and wheel all live on the ROOT and funnel
 * through the native input: value is written there and `input` (live) /
 * `change` (commit) events are dispatched from it, so consumers only ever
 * listen on the input. Keyboard (arrow keys) is handled natively.
 *
 * Tip text is root.dataset.format-driven ('time' | 'percent' | 'raw').
 * A host may set `rootEl.tipOverride` to a string or `(value) => string`
 * to replace the formatted text (the player uses this for seek targets).
 *
 * Setting input.value programmatically does NOT repaint; dispatch a bubbled
 * `input` event after writing the value.
 */

import { onReady } from '../support/ready';

const clamp = (value, low, high) => Math.min(high, Math.max(low, value));

/** Seconds -> m:ss (h:mm:ss past an hour), matching the PHP formatTime(). */
function formatTime(seconds) {
    if (!Number.isFinite(seconds)) return '--:--';

    const total = Math.max(0, Math.floor(seconds));
    const h = Math.floor(total / 3600);
    const m = Math.floor((total % 3600) / 60);
    const s = total % 60;

    return h > 0
        ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        : `${m}:${String(s).padStart(2, '0')}`;
}

function bounds(input) {
    const min = Number(input.min);
    const max = Number(input.max);
    const step = Number(input.step);

    return {
        min: Number.isFinite(min) ? min : 0,
        max: Number.isFinite(max) ? max : 100,
        step: Number.isFinite(step) && step > 0 ? step : 1,
    };
}

function tipText(root, input) {
    const override = root.tipOverride;

    if (typeof override === 'function') return override(Number(input.value));
    if (typeof override === 'string') return override;

    const value = Number(input.value);

    switch (root.dataset.format) {
        case 'percent':
            return `${Math.round(value)}%`;
        case 'time':
            return formatTime(value);
        default:
            return input.value;
    }
}

function paint(root, input) {
    const { min, max } = bounds(input);
    const percent = max > min ? ((Number(input.value) - min) / (max - min)) * 100 : 0;

    root.style.setProperty('--p', `${clamp(percent, 0, 100)}%`);

    const tip = root.querySelector('.slider-tip');

    if (tip) tip.textContent = tipText(root, input);
}

/** Snap raw to the input's step/min/max, write it and fire `type` from the input. */
function applyValue(root, input, raw, type) {
    const { min, max, step } = bounds(input);
    const snapped = Number(clamp(min + Math.round((raw - min) / step) * step, min, max).toFixed(4));

    if (type === 'input' && snapped === Number(input.value)) return;

    input.value = String(snapped);
    input.dispatchEvent(new Event(type, { bubbles: true }));
}

function valueFromPointer(root, input, clientX) {
    const { min, max } = bounds(input);
    const rect = root.getBoundingClientRect();
    const ratio = clamp((clientX - rect.left) / (rect.width || 1), 0, 1);

    return min + ratio * (max - min);
}

function bind(root) {
    if (root.dataset.bound) return;

    const input = root.querySelector('input[type=range]');

    if (!input) return;

    root.dataset.bound = '1';

    let dragging = false;

    input.addEventListener('input', () => paint(root, input));

    root.addEventListener('pointerdown', (event) => {
        // Suppress the native range's own drag so only this path moves --p.
        event.preventDefault();

        dragging = true;
        root.classList.add('is-active');
        root.setPointerCapture(event.pointerId);

        applyValue(root, input, valueFromPointer(root, input, event.clientX), 'input');
        input.focus({ preventScroll: true });
    });

    root.addEventListener('pointermove', (event) => {
        if (dragging) applyValue(root, input, valueFromPointer(root, input, event.clientX), 'input');
    });

    const commit = (event) => {
        if (dragging) applyValue(root, input, valueFromPointer(root, input, event.clientX), 'input');

        dragging = false;
        root.classList.remove('is-active');
        input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    root.addEventListener('pointerup', commit);
    root.addEventListener('pointercancel', () => {
        dragging = false;
        root.classList.remove('is-active');
    });

    root.addEventListener('lostpointercapture', () => {
        dragging = false;
        root.classList.remove('is-active');
    });

    root.addEventListener('wheel', (event) => {
        event.preventDefault();

        const { step } = bounds(input);

        applyValue(root, input, Number(input.value) + (event.deltaY < 0 ? step : -step), 'input');
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }, { passive: false });

    paint(root, input);
}

onReady(() => {
    document.querySelectorAll('[data-slider]').forEach(bind);
});
