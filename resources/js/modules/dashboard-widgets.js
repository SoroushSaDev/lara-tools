/**
 * Dashboard widget hydration — the parts of a widget only the browser knows.
 *
 * Contract with Blade views:
 *  - [data-widget-clock] live time, with data-format="12h|24h" and data-seconds="0|1"
 *  - [data-widget-date]  the date line that belongs to it
 *  - [data-widget-alarms] alarm summary, read from the Clock tool's storage
 *  - [data-widget-calc]  the Calculator's newest results, with
 *                        [data-widget-calc-empty] as the fallback line
 *
 * Every other widget row is server-rendered, so the cards read correctly
 * before this module runs; it only replaces placeholders and keeps time.
 */

import { onReady } from '../support/ready';
import { loadAlarms, nextArmed } from '../support/alarms';

const HISTORY_KEY = 'lara-tools.calc-history';

const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const MONTHS = [
    'January', 'February', 'March', 'April', 'May', 'June', 'July',
    'August', 'September', 'October', 'November', 'December',
];

const pad = (value) => String(value).padStart(2, '0');

let ticking = false;

function readJson(key) {
    try {
        return JSON.parse(localStorage.getItem(key) ?? 'null');
    } catch {
        return null;
    }
}

/** Honour the Clock preferences: 12/24h and optional seconds. */
function timeText(date, format, seconds) {
    const minutes = pad(date.getMinutes());
    const secs = seconds ? `:${pad(date.getSeconds())}` : '';

    if (format !== '12h') return `${pad(date.getHours())}:${minutes}${secs}`;

    const hours = date.getHours() % 12 || 12;

    return `${pad(hours)}:${minutes}${secs} ${date.getHours() < 12 ? 'AM' : 'PM'}`;
}

/** "07:30" stored by the Clock tool, shown in the widget's own format. */
function alarmTime(time, format) {
    return timeText(new Date(`2000-01-01T${time}:00`), format, false);
}

function renderClock(root) {
    const format = root.dataset.format ?? '24h';
    const seconds = root.dataset.seconds === '1';

    root.textContent = timeText(new Date(), format, seconds);

    const date = document.querySelector('[data-widget-date]');

    if (date) {
        const now = new Date();

        date.textContent = `${DAYS[now.getDay()]}, ${now.getDate()} ${MONTHS[now.getMonth()]}`;
    }
}

function renderAlarms(root) {
    const items = loadAlarms();

    if (items.length === 0) return;

    const armedCount = items.filter((alarm) => alarm.enabled).length;
    const format = document.querySelector('[data-widget-clock]')?.dataset.format ?? '24h';
    const plural = (count) => `${count} ${count === 1 ? 'alarm' : 'alarms'}`;

    // nextArmed only looks at days the alarm actually repeats on, so a
    // Mon-Fri alarm is not announced as the next one over the weekend.
    const next = armedCount > 0 ? nextArmed(items, new Date()) : null;

    if (next) {
        const when = next.offset === 0 ? '' : next.offset === 1 ? ' tomorrow' : ` in ${next.offset} days`;

        root.textContent = `${plural(armedCount)} armed · next ${alarmTime(next.time, format)}${when}`;
    } else {
        root.textContent = armedCount > 0
            ? `${plural(armedCount)} armed`
            : `${plural(items.length)} set · none armed`;
    }

    root.hidden = false;
}

function renderCalculation(root) {
    const stored = readJson(HISTORY_KEY);

    if (! Array.isArray(stored)) return;

    const entries = stored
        .filter((entry) => entry?.expression && entry?.result !== undefined && entry?.result !== null)
        .slice(0, 3);

    if (entries.length === 0) return;

    root.textContent = '';

    entries.forEach((entry) => {
        const line = document.createElement('p');
        line.className = 'widget-line';

        const expression = document.createElement('span');
        expression.className = 'block truncate text-[11px] font-bold text-slate-400 dark:text-slate-500';
        expression.textContent = entry.expression;

        const result = document.createElement('span');
        result.className = 'heading block truncate text-lg tabular-nums';
        result.textContent = entry.result;

        line.append(expression, result);
        root.append(line);
    });

    root.hidden = false;

    const empty = document.querySelector('[data-widget-calc-empty]');

    if (empty) empty.hidden = true;
}

onReady(() => {
    document.querySelectorAll('[data-widget-clock]').forEach(renderClock);
    document.querySelectorAll('[data-widget-alarms]').forEach(renderAlarms);
    document.querySelectorAll('[data-widget-calc]').forEach(renderCalculation);

    if (ticking || ! document.querySelector('[data-widget-clock]')) return;

    ticking = true;

    setInterval(() => {
        const clock = document.querySelector('[data-widget-clock]');

        if (clock?.isConnected) renderClock(clock);
    }, 1000);
});
