/**
 * Clock tool — four tabs driven by one ticking loop:
 *   world     local analog + digital clock and the pinned world clocks
 *   stopwatch lap-capable stopwatch
 *   timer     countdown with presets and an audible alert
 *   alarms    localStorage-backed alarms that ring while the tool is open
 *
 * Contract with the Blade view (#clock-root):
 *   data-clock-format="24h|12h" data-show-seconds="0|1"
 *   Analog hands:      #hand-hour #hand-minute #hand-second
 *   Digital readout:   #digital-time (+ #digital-seconds inside),
 *                      #digital-date
 *   World clocks:      [data-zone-time][data-tz]
 *   Stopwatch:         #sw-display, #sw-start, #sw-lap, #sw-reset, #sw-laps
 *   Timer:             #timer-display, #timer-bar, #timer-minutes,
 *                      #timer-seconds, #timer-start, #timer-reset,
 *                      preset chips [data-timer-preset]
 *   Alarms:            #alarm-form, #alarm-time, #alarm-label, #alarm-repeat,
 *                      #alarm-list, #alarm-empty, #alarm-alert,
 *                      #alarm-alert-text, #alarm-dismiss
 */

import { onReady } from '../support/ready';

const ALARMS_KEY = 'lara-tools.alarms';

let root = null;

const clock = {
    handHour: null,
    handMinute: null,
    handSecond: null,
    digitalTime: null,
    digitalSeconds: null,
    digitalDate: null,
};

const stopwatch = {
    running: false,
    startedAt: 0,
    elapsed: 0,
    laps: [],
    display: null,
    lapsEl: null,
};

const timer = {
    totalMs: 0,
    endsAt: 0,
    remainingMs: 0,
    running: false,
    finished: false,
};

const alarms = {
    items: [],
    list: null,
    empty: null,
    alert: null,
    alertText: null,
};

/** Pad a number to two digits. */
function pad(value) {
    return String(value).padStart(2, '0');
}

/** Attach a listener only once per node and event, surviving Turbo DOM morphs. */
function bindOnce(el, event, handler) {
    const flag = `bound${event}`;

    if (!el || el.dataset[flag]) return;

    el.dataset[flag] = '1';
    el.addEventListener(event, handler);
}

/** Split a Date into display parts honouring the 12/24h preference. */
function readClockParts(now) {
    const use24h = root.dataset.clockFormat !== '12h';
    let hours = now.getHours();
    let suffix = '';

    if (!use24h) {
        suffix = hours >= 12 ? ' PM' : ' AM';
        hours = hours % 12 || 12;
    }

    return {
        hours,
        minutes: now.getMinutes(),
        seconds: now.getSeconds(),
        suffix,
    };
}

function renderClock() {
    const now = new Date();
    const { hours, minutes, seconds, suffix } = readClockParts(now);
    const showSeconds = root.dataset.showSeconds === '1';

    if (clock.digitalTime) {
        const base = `${pad(hours)}:${pad(minutes)}`;
        const secondsPart = showSeconds ? `:${pad(seconds)}` : '';

        // The seconds digits sit in their own span so they can blink softly.
        if (showSeconds && clock.digitalSeconds && clock.digitalTime.contains(clock.digitalSeconds)) {
            clock.digitalTime.childNodes[0].textContent = `${base}:`;
            clock.digitalSeconds.textContent = pad(seconds);
        } else {
            clock.digitalTime.textContent = base + secondsPart;
        }

        if (clock.digitalDate) {
            clock.digitalDate.textContent = now.toLocaleDateString(undefined, {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
            }) + suffix;
        }
    }

    if (clock.handHour && clock.handMinute && clock.handSecond) {
        const ms = now.getMilliseconds();

        clock.handHour.style.transform = `rotate(${((hours % 12) + minutes / 60) * 30}deg)`;
        clock.handMinute.style.transform = `rotate(${(minutes + seconds / 60) * 6}deg)`;
        clock.handSecond.style.transform = `rotate(${(seconds + ms / 1000) * 6}deg)`;
    }
}

/* ------------------------------------------------------------------ */
/* Stopwatch                                                           */
/* ------------------------------------------------------------------ */

function renderStopwatch() {
    if (!stopwatch.display) return;

    const totalCs = Math.floor(stopwatch.elapsed / 10);
    const cs = totalCs % 100;
    const totalS = Math.floor(totalCs / 100);
    const s = totalS % 60;
    const m = Math.floor(totalS / 60);

    stopwatch.display.textContent = `${pad(m)}:${pad(s)}.${pad(cs)}`;
}

function stopWatchTick() {
    if (stopwatch.running) {
        stopwatch.elapsed = performance.now() - stopwatch.startedAt;
        renderStopwatch();
    }
}

function bindStopwatch() {
    const startBtn = document.getElementById('sw-start');

    bindOnce(startBtn, 'click', () => {
        stopwatch.running = !stopwatch.running;

        if (stopwatch.running) {
            stopwatch.startedAt = performance.now() - stopwatch.elapsed;
            startBtn.innerHTML = '<i class="bi bi-pause-fill"></i>Pause';
            startBtn.classList.add('!from-amber-500', '!to-orange-600');
        } else {
            startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Resume';
            startBtn.classList.remove('!from-amber-500', '!to-orange-600');
        }
    });

    bindOnce(document.getElementById('sw-lap'), 'click', () => {
        if (!stopwatch.running && stopwatch.elapsed === 0) return;

        stopwatch.laps.unshift(stopwatch.elapsed);

        const li = document.createElement('li');

        li.className = 'flex items-center justify-between glass px-4 py-2 text-sm animate-fade-up';

        li.innerHTML = `<span class="font-bold text-slate-500 dark:text-slate-400">Lap ${stopwatch.laps.length}</span>`
            + `<span class="font-display font-semibold tabular-nums">${stopwatch.display.textContent}</span>`;

        stopwatch.lapsEl?.prepend(li);
    });

    bindOnce(document.getElementById('sw-reset'), 'click', () => {
        stopwatch.running = false;
        stopwatch.elapsed = 0;
        stopwatch.laps = [];

        if (startBtn) {
            startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Start';
            startBtn.classList.remove('!from-amber-500', '!to-orange-600');
        }

        if (stopwatch.lapsEl) stopwatch.lapsEl.innerHTML = '';
        renderStopwatch();
    });
}

/* ------------------------------------------------------------------ */
/* Timer                                                               */
/* ------------------------------------------------------------------ */

function beep(times = 3) {
    try {
        const context = new AudioContext();
        const gain = context.createGain();

        gain.connect(context.destination);
        gain.gain.value = 0.08;

        for (let i = 0; i < times; i++) {
            const osc = context.createOscillator();

            osc.type = 'sine';
            osc.frequency.value = 880;
            osc.connect(gain);
            osc.start(context.currentTime + i * 0.35);
            osc.stop(context.currentTime + i * 0.35 + 0.18);
        }
    } catch {
        // Audio unavailable (autoplay policy); the visual flash still fires.
    }
}

function renderTimer() {
    const display = document.getElementById('timer-display');
    const bar = document.getElementById('timer-bar');

    if (!display) return;

    const ms = Math.max(0, timer.remainingMs);
    const totalS = Math.ceil(ms / 1000);

    display.textContent = `${pad(Math.floor(totalS / 60))}:${pad(totalS % 60)}`;

    if (bar) bar.style.width = timer.totalMs > 0 ? `${(ms / timer.totalMs) * 100}%` : '0%';
}

function timerTick() {
    if (!timer.running) return;

    timer.remainingMs = timer.endsAt - performance.now();

    if (timer.remainingMs <= 0) {
        timer.remainingMs = 0;
        timer.running = false;
        timer.finished = true;

        renderTimer();
        beep();

        const panel = document.querySelector('[data-tab-panel="timer"]');

        panel?.classList.add('animate-pop-in');

        const startBtn = document.getElementById('timer-start');

        if (startBtn) startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Start';

        return;
    }

    renderTimer();
}

function bindTimer() {
    const startBtn = document.getElementById('timer-start');

    bindOnce(startBtn, 'click', () => {
        if (timer.running) {
            // Pause.
            timer.running = false;
            timer.remainingMs = timer.endsAt - performance.now();
            startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Resume';

            return;
        }

        if (timer.finished || timer.remainingMs <= 0) {
            const minutes = Number(document.getElementById('timer-minutes')?.value || 0);
            const seconds = Number(document.getElementById('timer-seconds')?.value || 0);

            timer.totalMs = (minutes * 60 + seconds) * 1000;

            if (timer.totalMs <= 0) return;

            timer.finished = false;
            timer.remainingMs = timer.totalMs;
        }

        timer.endsAt = performance.now() + timer.remainingMs;
        timer.running = true;
        startBtn.innerHTML = '<i class="bi bi-pause-fill"></i>Pause';
    });

    bindOnce(document.getElementById('timer-reset'), 'click', () => {
        timer.running = false;
        timer.finished = false;
        timer.remainingMs = 0;
        timer.totalMs = 0;

        if (startBtn) startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Start';
        renderTimer();
    });

    document.querySelectorAll('[data-timer-preset]').forEach((chip) => {
        bindOnce(chip, 'click', () => {
            const minutesInput = document.getElementById('timer-minutes');
            const secondsInput = document.getElementById('timer-seconds');

            if (minutesInput) minutesInput.value = chip.dataset.timerPreset;
            if (secondsInput) secondsInput.value = 0;
        });
    });
}

/* ------------------------------------------------------------------ */
/* World clocks                                                        */
/* ------------------------------------------------------------------ */

function renderWorld() {
    document.querySelectorAll('[data-zone-time]').forEach((el) => {
        const now = new Date();

        try {
            const parts = new Intl.DateTimeFormat('en-GB', {
                timeZone: el.dataset.tz,
                hour: '2-digit',
                minute: '2-digit',
                ...(root?.dataset.showSeconds === '1' ? { second: '2-digit' } : {}),
                hour12: root?.dataset.clockFormat === '12h',
            }).formatToParts(now);

            const pick = (type) => parts.find((p) => p.type === type)?.value ?? '';
            const dayPart = parts.find((p) => p.type === 'dayPeriod')?.value ?? '';

            el.textContent = `${pick('hour')}:${pick('minute')}`
                + (root?.dataset.showSeconds === '1' ? `:${pick('second')}` : '')
                + (dayPart ? ` ${dayPart}` : '');
        } catch {
            el.textContent = '—';
        }
    });
}

/* ------------------------------------------------------------------ */
/* Alarms                                                              */
/* ------------------------------------------------------------------ */

/** Render "07:30" honouring the 12/24h preference. */
function formatAlarmTime(time) {
    if (root?.dataset.clockFormat !== '12h') return time;

    const [hours, minutes] = time.split(':').map(Number);

    return `${(hours % 12) || 12}:${pad(minutes)} ${hours >= 12 ? 'PM' : 'AM'}`;
}

function loadAlarms() {
    try {
        const stored = JSON.parse(localStorage.getItem(ALARMS_KEY) ?? '[]');

        return Array.isArray(stored)
            ? stored.filter((alarm) => /^\d{2}:\d{2}$/.test(alarm?.time))
            : [];
    } catch {
        return [];
    }
}

function saveAlarms() {
    try {
        localStorage.setItem(ALARMS_KEY, JSON.stringify(alarms.items));
    } catch {
        // Storage unavailable; the alarms still work for this session.
    }
}

function renderAlarms() {
    if (!alarms.list) return;

    alarms.list.textContent = '';
    alarms.empty?.classList.toggle('hidden', alarms.items.length > 0);

    alarms.items.forEach((alarm, index) => {
        const item = document.createElement('li');

        item.className = `glass flex items-center gap-4 p-4 ${alarm.enabled ? '' : 'opacity-55'}`;

        const text = document.createElement('div');

        text.className = 'min-w-0 flex-1';

        const time = document.createElement('p');

        time.className = 'heading text-2xl tabular-nums';
        time.textContent = formatAlarmTime(alarm.time);

        const label = document.createElement('p');

        label.className = 'truncate text-xs font-bold text-slate-400 dark:text-slate-500';
        label.textContent = [alarm.label || 'Alarm', alarm.repeat ? 'Every day' : 'Once'].join(' · ');

        text.append(time, label);

        const toggle = document.createElement('label');

        toggle.className = 'relative inline-flex shrink-0 cursor-pointer';
        toggle.innerHTML = '<span class="block h-7 w-12 rounded-full bg-slate-300 transition-colors'
            + ' peer-checked:bg-gradient-to-r peer-checked:from-violet-600 peer-checked:to-fuchsia-600 dark:bg-slate-600"></span>'
            + '<span class="pointer-events-none absolute left-1 top-1 size-5 rounded-full bg-white shadow-md'
            + ' transition-transform peer-checked:translate-x-5"></span>';

        const checkbox = document.createElement('input');

        checkbox.type = 'checkbox';
        checkbox.className = 'peer sr-only';
        checkbox.checked = alarm.enabled;
        checkbox.dataset.alarmToggle = index;
        checkbox.setAttribute('aria-label', `Enable ${alarm.label || 'alarm'}`);

        toggle.prepend(checkbox);

        const remove = document.createElement('button');

        remove.type = 'button';
        remove.dataset.alarmRemove = index;
        remove.className = 'icon-btn icon-btn-danger !size-9 shrink-0';
        remove.title = 'Delete alarm';
        remove.innerHTML = '<i class="bi bi-trash3"></i>';

        item.append(text, toggle, remove);
        alarms.list.appendChild(item);
    });
}

function showAlarmAlert(alarm) {
    if (!alarms.alert) return;

    alarms.alertText.textContent = `${formatAlarmTime(alarm.time)} · ${alarm.label || 'Alarm'}`;
    alarms.alert.classList.remove('hidden');
    alarms.alert.classList.add('flex');
}

function hideAlarmAlert() {
    alarms.alert?.classList.add('hidden');
    alarms.alert?.classList.remove('flex');
}

/** Ring every alarm whose minute has arrived and has not fired for it yet. */
function alarmTick() {
    const now = new Date();
    const hhmm = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
    const stamp = `${now.toDateString()} ${hhmm}`;
    let changed = false;

    alarms.items.forEach((alarm) => {
        if (!alarm.enabled || alarm.time !== hhmm || alarm.lastFired === stamp) return;

        alarm.lastFired = stamp;

        if (!alarm.repeat) alarm.enabled = false;

        changed = true;

        showAlarmAlert(alarm);
        beep(4);
    });

    if (changed) {
        saveAlarms();
        renderAlarms();
    }
}

function bindAlarms() {
    const form = document.getElementById('alarm-form');

    bindOnce(form, 'submit', (event) => {
        event.preventDefault();

        const timeInput = document.getElementById('alarm-time');
        const labelInput = document.getElementById('alarm-label');
        const repeatInput = document.getElementById('alarm-repeat');

        if (!timeInput.value) {
            timeInput.reportValidity();

            return;
        }

        alarms.items.push({
            time: timeInput.value,
            label: labelInput.value.trim(),
            repeat: repeatInput.checked,
            enabled: true,
            lastFired: '',
        });

        alarms.items.sort((a, b) => a.time.localeCompare(b.time));

        saveAlarms();
        renderAlarms();

        form.reset();
    });

    bindOnce(alarms.list, 'change', (event) => {
        const toggle = event.target.closest('[data-alarm-toggle]');

        if (!toggle) return;

        const alarm = alarms.items[Number(toggle.dataset.alarmToggle)];

        if (alarm) {
            alarm.enabled = toggle.checked;
            alarm.lastFired = '';

            saveAlarms();
            renderAlarms();
        }
    });

    bindOnce(alarms.list, 'click', (event) => {
        const button = event.target.closest('[data-alarm-remove]');

        if (!button) return;

        alarms.items.splice(Number(button.dataset.alarmRemove), 1);

        saveAlarms();
        renderAlarms();
    });

    bindOnce(document.getElementById('alarm-dismiss'), 'click', hideAlarmAlert);
}

/* ------------------------------------------------------------------ */
/* Bootstrap                                                           */
/* ------------------------------------------------------------------ */

let loopRunning = false;

onReady(() => {
    root = document.getElementById('clock-root');

    // Remove the previous page's tick targets; the shared interval keeps
    // running but no-ops until the clock page is visited again.
    clock.handHour = document.getElementById('hand-hour');
    clock.handMinute = document.getElementById('hand-minute');
    clock.handSecond = document.getElementById('hand-second');
    clock.digitalTime = document.getElementById('digital-time');
    clock.digitalSeconds = document.getElementById('digital-seconds');
    clock.digitalDate = document.getElementById('digital-date');

    if (!root) return;

    stopwatch.display = document.getElementById('sw-display');
    stopwatch.lapsEl = document.getElementById('sw-laps');

    alarms.list = document.getElementById('alarm-list');
    alarms.empty = document.getElementById('alarm-empty');
    alarms.alert = document.getElementById('alarm-alert');
    alarms.alertText = document.getElementById('alarm-alert-text');
    alarms.items = loadAlarms();
    alarms.items.sort((a, b) => a.time.localeCompare(b.time));

    bindStopwatch();
    bindTimer();
    bindAlarms();

    renderClock();
    renderStopwatch();
    renderTimer();
    renderAlarms();

    if (!loopRunning) {
        loopRunning = true;

        setInterval(() => {
            if (!root?.isConnected) return;

            if (clock.handSecond || clock.digitalTime) renderClock();
            stopWatchTick();
            timerTick();
            alarmTick();

            if (document.querySelector('[data-zone-time]')) renderWorld();
        }, 50);
    }
});
