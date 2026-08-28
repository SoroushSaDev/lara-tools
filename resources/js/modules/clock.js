/**
 * Clock tool — four tabs driven by one ticking loop:
 *   clock     analog + digital real-time clock
 *   stopwatch lap-capable stopwatch
 *   timer     countdown with presets and an audible alert
 *   world     world clocks persisted through /clock/zones (server-rendered)
 *
 * Contract with the Blade view (#clock-root):
 *   data-clock-format="24h|12h" data-show-seconds="0|1"
 *   Analog hands:      #hand-hour #hand-minute #hand-second
 *   Digital readout:   #digital-time (+ #digital-seconds inside),
 *                      #digital-date
 *   Stopwatch:         #sw-display, #sw-start, #sw-lap, #sw-reset, #sw-laps
 *   Timer:             #timer-display, #timer-bar, #timer-minutes,
 *                      #timer-seconds, #timer-start, #timer-reset,
 *                      preset chips [data-timer-preset]
 */

import { onReady } from '../support/ready';

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

/** Pad a number to two digits. */
function pad(value) {
    return String(value).padStart(2, '0');
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

    startBtn?.addEventListener('click', () => {
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

    document.getElementById('sw-lap')?.addEventListener('click', () => {
        if (!stopwatch.running && stopwatch.elapsed === 0) return;

        stopwatch.laps.unshift(stopwatch.elapsed);

        const li = document.createElement('li');

        li.className = 'flex items-center justify-between glass px-4 py-2 text-sm animate-fade-up';

        li.innerHTML = `<span class="font-bold text-slate-500 dark:text-slate-400">Lap ${stopwatch.laps.length}</span>`
            + `<span class="font-display font-semibold tabular-nums">${stopwatch.display.textContent}</span>`;

        stopwatch.lapsEl?.prepend(li);
    });

    document.getElementById('sw-reset')?.addEventListener('click', () => {
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

        const panel = document.getElementById('panel-timer');

        panel?.classList.add('animate-pop-in');

        const startBtn = document.getElementById('timer-start');

        if (startBtn) startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Start';

        return;
    }

    renderTimer();
}

function bindTimer() {
    const startBtn = document.getElementById('timer-start');

    startBtn?.addEventListener('click', () => {
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

    document.getElementById('timer-reset')?.addEventListener('click', () => {
        timer.running = false;
        timer.finished = false;
        timer.remainingMs = 0;
        timer.totalMs = 0;

        if (startBtn) startBtn.innerHTML = '<i class="bi bi-play-fill"></i>Start';
        renderTimer();
    });

    document.querySelectorAll('[data-timer-preset]').forEach((chip) => {
        chip.addEventListener('click', () => {
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

    bindStopwatch();
    bindTimer();

    renderClock();
    renderStopwatch();
    renderTimer();

    if (!loopRunning) {
        loopRunning = true;

        setInterval(() => {
            if (!root?.isConnected) return;

            if (clock.handSecond || clock.digitalTime) renderClock();
            stopWatchTick();
            timerTick();

            if (document.querySelector('[data-zone-time]')) renderWorld();
        }, 50);
    }
});
