/**
 * Reusable custom dropdown — enhances any native <select> marked with
 * [data-dropdown] into a styled, keyboard-accessible dropdown.
 *
 * The native <select> stays in the DOM (visually hidden, sr-only) as the single
 * source of truth: it keeps its `name`, `value`, `required` and server-side
 * form submission working, and other modules can keep reading/writing
 * `select.value` (the converter repopulates its options dynamically).
 *
 * Behaviour:
 *  - Opens above or below the trigger depending on available viewport space.
 *  - List caps its height and scrolls when there are many options.
 *  - Floats on a high z-index so it never renders under other content.
 *  - Closes on outside click, Esc, or after choosing.
 *  - A MutationObserver keeps the label in sync when options/value change
 *    programmatically (e.g. the unit converter switching categories).
 */

import { onReady } from '../support/ready';

let openDropdown = null;

/** Every enhanced select plus its body-mounted menu. */
const instances = [];

function optionLabel(select, option) {
    return option ? option.textContent.trim() : '';
}

/** Build the styled trigger button mirroring the select's look. */
function buildTrigger(select) {
    const label = document.createElement('span');

    label.className = 'dropdown-label min-w-0 flex-1 truncate text-left';

    const caret = document.createElement('i');

    caret.className = 'bi bi-chevron-down shrink-0 transition-transform duration-200 text-slate-400';

    const button = document.createElement('button');

    button.type = 'button';
    button.className = 'dropdown-trigger field-input flex w-full min-w-[140px] cursor-pointer items-center gap-2';
    button.appendChild(label);
    button.appendChild(caret);
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');

    return { button, label, caret };
}

/** Build the option list from the select's <option> elements. */
function buildMenu(select, onPick) {
    const menu = document.createElement('div');

    menu.className = 'dropdown-menu fixed left-0 top-0 z-[60] max-h-72 overflow-y-auto rounded-xl border border-slate-900/10 bg-white/95 p-1.5 shadow-2xl shadow-slate-900/20 backdrop-blur-xl animate-fade-in dark:border-white/10 dark:bg-slate-900/95 dark:shadow-black/50';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;

    const render = () => {
        menu.textContent = '';

        [...select.options].forEach((option) => {
            const item = document.createElement('button');

            item.type = 'button';
            item.className = 'dropdown-option flex w-full cursor-pointer items-center justify-between gap-2 rounded-lg px-3 py-2 text-left text-sm font-semibold text-slate-700 transition-colors hover:bg-violet-500/10 hover:text-violet-700 dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-fuchsia-300';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', String(option.value === String(select.value)));
            item.dataset.value = option.value;

            const text = document.createElement('span');

            text.className = 'min-w-0 truncate';
            text.textContent = option.label || option.textContent;

            item.appendChild(text);

            if (option.value === String(select.value)) {
                const check = document.createElement('i');

                check.className = 'bi bi-check2 shrink-0 text-violet-500 dark:text-fuchsia-300';
                item.appendChild(check);
            }

            item.addEventListener('click', (event) => {
                event.stopPropagation();
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                onPick();
            });

            menu.appendChild(item);
        });
    };

    render(); // populates immediately; called again on open for fresh values

    return { menu, render };
}

function close(instance, { focusTrigger = false } = {}) {
    if (!instance) return;

    openDropdown = null;

    const { button, menu } = instance;

    button.setAttribute('aria-expanded', 'false');
    menu.hidden = true;

    if (focusTrigger) button.focus();
}

function positionMenu(instance) {
    const rect = instance.button.getBoundingClientRect();
    const style = instance.menu.style;
    const menuWidth = Math.max(rect.width, 160);
    const gap = 8;

    style.width = `${menuWidth}px`;
    style.maxWidth = `${window.innerWidth - gap * 2}px`;

    // Keep horizontally inside the viewport.
    style.left = `${Math.max(gap, Math.min(rect.left, window.innerWidth - menuWidth - gap))}px`;

    // Peek the natural height (menu is already rendered when we get here).
    const menuHeight = Math.min(instance.menu.offsetHeight, 288); // 288px = max-h-72
    const spaceBelow = window.innerHeight - rect.bottom;
    const spaceAbove = rect.top;

    style.top = 'auto';
    style.bottom = 'auto';

    // Prefer opening below; flip above when there isn't enough space below.
    if (spaceBelow - gap >= menuHeight || spaceBelow >= spaceAbove) {
        style.top = `${Math.min(rect.bottom + gap, window.innerHeight - menuHeight - gap)}px`;
    } else {
        style.top = `${Math.max(gap, rect.top - menuHeight - gap)}px`;
    }
}

function open(instance) {
    close(openDropdown);

    openDropdown = instance;

    instance.menu.hidden = false;
    instance.render();
    positionMenu(instance);

    const { button } = instance;

    button.setAttribute('aria-expanded', 'true');

    const active = instance.menu.querySelector('[aria-selected="true"]');

    if (active) active.focus();
}

function enhance(select) {
    // Reuse existing .dropdown wrapper if present (e.g., from Blade), otherwise create one.
    let wrapper = select.parentElement;

    if (!wrapper?.classList.contains('dropdown')) {
        wrapper = document.createElement('div');
        wrapper.classList.add('dropdown', 'relative');
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
    } else {
        // Ensure it has the relative positioning.
        wrapper.classList.add('relative');
    }

    select.classList.add('sr-only');
    // The trigger button takes over the tab stop; keep the source-of-truth
    // select out of the tab order so focus stops are never doubled.
    select.tabIndex = -1;

    const { button, label } = buildTrigger(select);
    const syncLabel = () => {
        const selected = select.selectedOptions[0] || select.options[0];

        label.textContent = optionLabel(select, selected) || 'Select…';
    };
    const instance = {
        select,
        button,
        menu: null,
        render() {
            this.menuRender();
            const selected = select.selectedOptions[0] || select.options[0];

            if (selected) {
                const current = this.menu.querySelector(`[data-value="${CSS.escape(String(selected.value))}"]`);

                if (current) current.focus();
            }
        },
    };

    const { menu, render } = buildMenu(select, () => close(instance, { focusTrigger: true }));

    instance.menu = menu;
    instance.menuRender = render;
    wrapper.appendChild(button);

    // Menus mount on <body>: any ancestor with backdrop-filter/transform
    // (glass cards, animated sections) would otherwise become the
    // containing block for position:fixed and misplace the list.
    document.body.appendChild(menu);
    instances.push(instance);

    // Roving focus through the listbox with the arrow keys.
    menu.addEventListener('keydown', (event) => {
        const items = [...menu.querySelectorAll('.dropdown-option')];
        const current = items.indexOf(document.activeElement);
        let next = null;

        switch (event.key) {
            case 'ArrowDown': next = items[Math.min(current + 1, items.length - 1)]; break;
            case 'ArrowUp': next = items[Math.max(current - 1, 0)]; break;
            case 'Home': next = items[0]; break;
            case 'End': next = items[items.length - 1]; break;
            default: return;
        }

        event.preventDefault();
        next?.focus();
    });

    button.addEventListener('click', () => {
        if (openDropdown === instance) {
            close(instance, { focusTrigger: true });

            return;
        }

        open(instance);
    });

    // Keep label in sync when the value/options change (incl. programmatically).
    select.addEventListener('change', syncLabel);
    syncLabel();

    const observer = new MutationObserver(syncLabel);

    observer.observe(select, { attributes: true, attributeFilter: ['value'], childList: true, subtree: true });

    if (select.autofocus) button.focus();
}

function refresh() {
    // Drop menus whose select disappeared with a previous page.
    for (let i = instances.length - 1; i >= 0; i--) {
        if (!instances[i].select.isConnected) {
            instances[i].menu.remove();
            instances.splice(i, 1);
        }
    }

    document.querySelectorAll('select[data-dropdown]').forEach((select) => {
        if (select.dataset.dropdownEnhanced) return;

        select.dataset.dropdownEnhanced = '1';
        enhance(select);
    });
}

// Global event that applies to every enhanced dropdown.
document.addEventListener('click', (event) => {
    if (!openDropdown) return;

    // The menu now lives on <body>, so it must also count as "inside".
    if (!event.target.closest('.dropdown') && !event.target.closest('.dropdown-menu')) {
        close(openDropdown);
    }
});

document.addEventListener('keydown', (event) => {
    if (!openDropdown) return;

    if (event.key === 'Escape') {
        close(openDropdown, { focusTrigger: true });
    }
});

window.addEventListener('resize', () => {
    if (openDropdown) positionMenu(openDropdown);
});

window.addEventListener('scroll', () => {
    if (openDropdown) positionMenu(openDropdown);
}, true);

onReady(refresh);
