/**
 * Alarm rows shared by the Clock tool (which writes them) and the dashboard
 * widget (which reads them), so both agree on when an alarm is due.
 *
 * Row shape, kept in localStorage under `lara-tools.alarms`:
 *   { time: "HH:MM", label, days, enabled, lastFired }
 *
 * `days` lists the weekdays it repeats on, numbered like Date.getDay()
 * (0 = Sunday). An empty list means a one-time alarm: it rings on the day it
 * was set and switches itself off.
 */

const ALARMS_KEY = 'lara-tools.alarms';

const ALL_DAYS = [0, 1, 2, 3, 4, 5, 6];

/** Every weekday, for the form's 'Every day' master toggle. */
export { ALL_DAYS };

/** Days behind the preset rows written by older builds. */
const LEGACY_WEEKDAYS = [1, 2, 3, 4, 5];

/** Short names, Sunday first — the same order the calendar grid uses. */
export const DAY_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

/** Rows written before the day chips existed stored a mode or a `repeat` flag. */
function normalise(alarm) {
    const { mode, repeat, ...row } = alarm;
    const listed = (row.days ?? []).filter((day) => Number.isInteger(day) && day >= 0 && day <= 6);

    let days = listed;

    if (mode === 'weekdays') {
        days = LEGACY_WEEKDAYS;
    } else if (row.days === undefined) {
        // A row with no list at all used to repeat: 'days' mode meant every day,
        // and so did the old daily switch. Today an empty list means one-time.
        days = (mode === 'days' || repeat === true) ? ALL_DAYS : [];
    }

    return { ...row, days: [...days] };
}

/** Every valid stored row, newest schema applied to older records. */
export function loadAlarms() {
    let stored = [];

    try {
        stored = JSON.parse(localStorage.getItem(ALARMS_KEY) ?? '[]');
    } catch {
        return [];
    }

    return Array.isArray(stored)
        ? stored.filter((alarm) => /^\d{2}:\d{2}$/.test(alarm?.time ?? '')).map(normalise)
        : [];
}

export function saveAlarms(items) {
    try {
        localStorage.setItem(ALARMS_KEY, JSON.stringify(items));
    } catch {
        // Storage unavailable; the alarms still work for this session.
    }
}

/** Whether the alarm is due on that calendar day. */
export function appliesOn(alarm, date) {
    return alarm.days.length === 0 || alarm.days.includes(date.getDay());
}

/** "Once", "Every day" or the ticked day names. */
export function describeAlarm(alarm) {
    if (alarm.days.length === 0) return 'Once';
    if (alarm.days.length === 7) return 'Every day';

    return [...alarm.days].sort((a, b) => a - b).map((day) => DAY_SHORT[day]).join(', ');
}

/**
 * The soonest armed alarm still ahead of `now`, as { time, offset } where
 * offset is whole days from today, or null when nothing is armed.
 */
export function nextArmed(items, now) {
    const hhmm = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    const armed = items.filter((alarm) => alarm.enabled);

    for (let offset = 0; offset <= 7; offset++) {
        const date = new Date(now);

        date.setDate(now.getDate() + offset);

        const times = armed
            .filter((alarm) => appliesOn(alarm, date))
            .map((alarm) => alarm.time)
            .filter((time) => offset > 0 || time > hhmm)
            .sort();

        if (times.length > 0) {
            return { time: times[0], offset };
        }
    }

    return null;
}
