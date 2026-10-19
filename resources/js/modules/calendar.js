/**
 * Calendar month grid.
 *
 * The server renders the shell (weekday headers, selects, event/holiday
 * lists); this module draws the day cells and wires navigation. Changing
 * the month/year or picking a day performs a full navigation so the server
 * can re-fetch holidays and events for the new range.
 *
 * Contract with the Blade view:
 *  - Root:        #calendar-root[data-date="YYYY-MM-DD"][data-event-days='[...]'
 *                 [data-holiday-days='{"day":["title",...]}"][data-week-start="0|1"]
 *  - Grid:        #calendar-grid (weekday headers rendered here too)
 */

import { onReady } from '../support/ready';

const WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

let selected = null;
let grid = null;
let baseUrl = '/calendar';
let weekStart = 0;
let holidayMap = {};

function navigate(date) {
    const pad = (n) => String(n).padStart(2, '0');

    window.location.href = `${baseUrl}?date=${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function isSameDay(a, b) {
    return a && b && a.getDate() === b.getDate()
        && a.getMonth() === b.getMonth()
        && a.getFullYear() === b.getFullYear();
}

/** Shift an index into week-start space (0 = Sunday or Monday). */
function weekdayIndex(day) {
    return (day - weekStart + 7) % 7;
}

function buildCell(day, month, year, options = {}) {
    const cell = document.createElement('button');

    cell.type = 'button';
    cell.textContent = day > 0 ? day : '';
    cell.className = [
        'relative flex h-11 items-center justify-center rounded-xl text-sm font-bold transition-all duration-200 sm:h-12 sm:text-base',
        'cursor-pointer select-none',
        'hover:scale-105 hover:bg-violet-500/20 active:scale-95',
        day > 0 ? '' : 'pointer-events-none',
    ].join(' ');

    if (day === 0) {
        cell.disabled = true;
        cell.classList.remove('hover:scale-105', 'hover:bg-violet-500/20');

        return cell;
    }

    const date = new Date(year, month, day);
    const today = new Date();

    if (isSameDay(date, today)) {
        cell.classList.add('ring-2', 'ring-violet-500');
    }

    if (options.isSelected) {
        cell.classList.add('bg-gradient-to-br', 'from-violet-600', 'to-fuchsia-600',
            'text-white', 'shadow-lg', 'shadow-violet-600/40');
    } else {
        cell.classList.add('bg-white/50', 'dark:bg-white/[0.07]');
    }

    if ((options.eventDays ?? []).includes(day)) {
        const dot = document.createElement('span');

        dot.className = 'absolute bottom-1.5 left-1/2 size-1.5 -translate-x-1/2 rounded-full '
            + (options.isSelected ? 'bg-white' : 'bg-fuchsia-500');

        cell.appendChild(dot);
    }

    const holidays = options.holidays ?? [];

    if (holidays.length > 0) {
        const dot = document.createElement('span');

        dot.className = 'absolute bottom-1.5 right-1.5 size-1.5 rounded-full '
            + (options.isSelected ? 'bg-white/90' : 'bg-sky-400');

        cell.appendChild(dot);
        cell.title = holidays.join('\n');
    }

    cell.addEventListener('click', () => navigate(date));

    return cell;
}

function render(year, month, eventDays) {
    grid.innerHTML = '';

    // Weekday headers honour the configured first day of the week.
    WEEKDAYS.forEach((label, index) => {
        const header = document.createElement('div');

        header.textContent = WEEKDAYS[(weekStart + index) % 7];
        header.className = 'text-center text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500';
        grid.appendChild(header);
    });

    // Leading blanks so the 1st lands on the correct weekday.
    const firstWeekday = weekdayIndex(new Date(year, month - 1, 1).getDay());
    const daysInMonth = new Date(year, month, 0).getDate();

    for (let i = 0; i < firstWeekday; i++) {
        grid.appendChild(buildCell(0));
    }

    for (let day = 1; day <= daysInMonth; day++) {
        grid.appendChild(buildCell(day, month - 1, year, {
            isSelected: isSameDay(new Date(year, month - 1, day), selected),
            eventDays,
            holidays: holidayMap[day] ?? [],
        }));
    }
}

onReady(() => {
    const root = document.getElementById('calendar-root');

    if (!root) {
        // Not on the calendar page; nothing to draw.
        grid = null;

        return;
    }

    grid = document.getElementById('calendar-grid');
    baseUrl = root.dataset.baseUrl || '/calendar';
    weekStart = Number(root.dataset.weekStart || '0');
    selected = new Date(`${root.dataset.date}T00:00:00`);

    const eventDays = JSON.parse(root.dataset.eventDays || '[]');

    try {
        holidayMap = JSON.parse(root.dataset.holidayDays || '{}');
    } catch {
        holidayMap = {};
    }

    render(selected.getFullYear(), selected.getMonth() + 1, eventDays);

    // Month/year selects and neighbour buttons navigate to reload events.
    const monthSelect = document.getElementById('calendar-month');
    const yearSelect = document.getElementById('calendar-year');

    const navigateToSelection = () => {
        navigate(new Date(Number(yearSelect.value), Number(monthSelect.value) - 1, Math.min(selected.getDate(), 28)));
    };

    // Turbo morphs keep unchanged nodes, so guard against binding twice.
    if (monthSelect && !monthSelect.dataset.bound) {
        monthSelect.dataset.bound = '1';
        monthSelect.addEventListener('change', navigateToSelection);
    }

    if (yearSelect && !yearSelect.dataset.bound) {
        yearSelect.dataset.bound = '1';
        yearSelect.addEventListener('change', navigateToSelection);
    }

    document.querySelectorAll('[data-calendar-shift]').forEach((button) => {
        if (button.dataset.bound) return;

        button.dataset.bound = '1';

        button.addEventListener('click', () => {
            const shift = Number(button.dataset.calendarShift);
            const target = new Date(selected.getFullYear(), selected.getMonth() + shift, 1);

            // Clamp the day so e.g. Mar 31 shifts to Apr 30, never May 1.
            const daysInTarget = new Date(target.getFullYear(), target.getMonth() + 1, 0).getDate();

            target.setDate(Math.min(selected.getDate(), daysInTarget));

            navigate(target);
        });
    });
});
