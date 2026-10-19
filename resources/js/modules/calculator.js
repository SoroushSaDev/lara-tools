/**
 * Calculator engine with four modes: Standard, Scientific, BMI and Date.
 *
 * Standard and Scientific share one expression string, one tokenizer +
 * shunting-yard evaluator (no eval() / new Function()) and one localStorage
 * history. BMI and Date are self-contained panels in the same [data-tabs]
 * root driven by the generic tabs module.
 *
 * Contract with the Blade view:
 *  - Root:          #calculator, also the [data-tabs] root
 *  - Displays:      #calc-expression/#calc-preview (standard),
 *                   #sci-expression/#sci-preview (scientific)
 *  - Keypad:        [data-key="0-9 . + - * / % ^ ! ( ) sin( ... mod ^(-1) |
 *                   clear | backspace | equals"]; digits/operators reuse the
 *                   standard keypad rendered inside both calc panels
 *  - Angle toggle:  #calc-angle-toggle [data-action="angle-toggle"] (DEG/RAD)
 *  - History:       #calc-history-aside, #calc-history, #calc-history-clear,
 *                   entries as [data-history-index]
 *  - BMI:           #bmi-panel, [data-bmi-unit="metric|imperial"] pills,
 *                   [data-bmi-fields] groups with [data-bmi-role="weight|height"]
 *                   inputs, #bmi-result, #bmi-value, #bmi-category, #bmi-range,
 *                   #bmi-error
 *  - Date:          #date-panel, #date-from, #date-to, #date-result, #date-ymd,
 *                   #date-weeks, #date-days, #date-note, #date-error
 *  - Storage:       lara-tools.calc-history (pre-existing), lara-tools.calc.mode,
 *                   lara-tools.calc.angles, lara-tools.calc.bmi-unit
 */

import { onReady } from '../support/ready';

const STORAGE = {
    history: 'lara-tools.calc-history',
    mode: 'lara-tools.calc.mode',
    angles: 'lara-tools.calc.angles',
    bmiUnit: 'lara-tools.calc.bmi-unit',
};

const HISTORY_LIMIT = 30;

// Binary/infix operators. '^' and unary '-' ('u') share precedence 4 and are
// both right-associative so that 2^-3 parses as 2^(-3) while -2^2 = -(2^2).
const BINARY = {
    '+': { precedence: 1 },
    '-': { precedence: 1 },
    '*': { precedence: 2 },
    '/': { precedence: 2 },
    '%': { precedence: 2 },
    '^': { precedence: 4, right: true },
    u: { precedence: 4, right: true },
};

// Characters the keypad appends one at a time.
const OPERATORS = Object.keys(BINARY).filter((op) => op !== 'u');

const FUNCTIONS = new Set(['sin', 'cos', 'tan', 'asin', 'acos', 'atan', 'ln', 'log10', 'exp', 'sqrt', 'cbrt']);
const CONSTANTS = { 'π': Math.PI, e: Math.E };

// Longest-first so "log10" wins over "log" and "asin" over "sin".
const NAMES = [...FUNCTIONS, ...Object.keys(CONSTANTS), 'mod'].sort((a, b) => b.length - a.length);

// Multi-char insertions that Backspace should delete as one unit; longer
// names first so "asin(" is not eaten as "sin(".
const ATOMIC_TAILS = ['^(-1)', ' mod ', 'asin(', 'acos(', 'atan(', 'log10(', 'sqrt(', 'cbrt(', 'sin(', 'cos(', 'tan(', 'exp(', 'ln(', '^2', '^3'];

const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const BMI_TONES = {
    Underweight: ['bg-sky-500/15', 'text-sky-600', 'dark:text-sky-300'],
    Normal: ['bg-emerald-500/15', 'text-emerald-600', 'dark:text-emerald-300'],
    Overweight: ['bg-amber-500/15', 'text-amber-600', 'dark:text-amber-300'],
    Obese: ['bg-rose-500/15', 'text-rose-600', 'dark:text-rose-300'],
};

const state = {
    expression: '',
    error: false,
    angleMode: 'deg',
    bmiUnit: 'metric',
};

let activeMode = 'standard';
let angleToggle = null;
let aside = null;
let bmiError = null;
let bmiResultBox = null;
let bmiValueEl = null;
let bmiCategoryEl = null;
let bmiRangeEl = null;
let dateError = null;
let dateResultBox = null;
let dateNote = null;
let dateYmd = null;
let dateWeeks = null;
let dateDays = null;
let keyboardBound = false;
let history = [];
let historyList = null;
let historyEmpty = null;

// [{ display, preview }] — standard and scientific pane pairs.
let panes = [];

function readStored(key) {
    try {
        return localStorage.getItem(key);
    } catch {
        return null;
    }
}

function writeStored(key, value) {
    try {
        localStorage.setItem(key, value);
    } catch {
        // Storage unavailable (private mode / quota); settings stay in memory.
    }
}

/* ------------------------------------------------------------------ engine */

function requireDomain(condition) {
    if (!condition) throw new Error('domain error');
}

/** Split the raw expression into typed tokens, inserting implicit products. */
function tokenize(input) {
    const tokens = [];
    let i = 0;

    const endsValue = (token) => token
        && (token.kind === 'num' || token.kind === 'const' || token.kind === 'rp' || token.kind === 'fact');

    const push = (token) => {
        const previous = tokens[tokens.length - 1];

        // 2π, 3(4), (1+1)2, 5sin(2) ... all mean multiplication.
        if (endsValue(previous) && (token.kind === 'num' || token.kind === 'const' || token.kind === 'func' || token.kind === 'lp')) {
            tokens.push({ kind: 'op', value: '*' });
        }

        tokens.push(token);
    };

    while (i < input.length) {
        const char = input[i];

        if (/\s/.test(char)) {
            i += 1;
            continue;
        }

        if ((char >= '0' && char <= '9') || char === '.') {
            let raw = '';

            while (i < input.length && ((input[i] >= '0' && input[i] <= '9') || input[i] === '.')) raw += input[i++];

            const value = Number(raw);

            requireDomain(raw !== '.' && raw !== '' && Number.isFinite(value));
            push({ kind: 'num', value });
            continue;
        }

        if (char === '(') {
            i += 1;
            push({ kind: 'lp' });
            continue;
        }

        if (char === ')') {
            i += 1;
            tokens.push({ kind: 'rp' });
            continue;
        }

        if (char === '!') {
            i += 1;
            tokens.push({ kind: 'fact' });
            continue;
        }

        if (char === '+' || char === '-' || char === '*' || char === '/' || char === '%' || char === '^') {
            i += 1;

            const previous = tokens[tokens.length - 1];
            const unary = (char === '-' || char === '+') && (!previous || previous.kind === 'op' || previous.kind === 'lp');

            if (unary) {
                // Unary plus changes nothing; unary minus becomes an operator.
                if (char === '-') tokens.push({ kind: 'op', value: 'u' });
                continue;
            }

            tokens.push({ kind: 'op', value: char });
            continue;
        }

        // Longest known name match: functions (log10), constants (π, e), mod.
        const rest = input.slice(i).toLowerCase();
        const name = NAMES.find((known) => rest.startsWith(known));

        if (name === 'mod') {
            i += name.length;
            tokens.push({ kind: 'op', value: '%' });
            continue;
        }

        if (name && Object.hasOwn(CONSTANTS, name)) {
            i += name.length;
            push({ kind: 'const', value: CONSTANTS[name] });
            continue;
        }

        if (name) {
            i += name.length;
            tokens.push({ kind: 'func', name });
            continue;
        }

        // Anything else is garbage; fail cleanly into the error display.
        throw new Error('bad token');
    }

    return tokens;
}

/** Convert infix tokens to reverse-polish notation. */
function toRpn(tokens) {
    const output = [];
    const stack = [];

    for (const token of tokens) {
        if (token.kind === 'num' || token.kind === 'const') {
            output.push(token.value);
            continue;
        }

        if (token.kind === 'fact') {
            // Postfix: binds to the value just read, emit immediately.
            output.push(token);
            continue;
        }

        if (token.kind === 'func' || token.kind === 'lp') {
            stack.push(token);
            continue;
        }

        if (token.kind === 'rp') {
            while (stack.length > 0 && stack[stack.length - 1].kind !== 'lp') output.push(stack.pop());

            requireDomain(stack.length > 0);
            stack.pop();

            if (stack.length > 0 && stack[stack.length - 1].kind === 'func') output.push(stack.pop());
            continue;
        }

        // Binary/unary operator: pop strictly-stronger (or equal, if the new
        // operator is left-associative) operators off the stack first.
        const meta = BINARY[token.value];

        while (stack.length > 0) {
            const top = stack[stack.length - 1];

            if (top.kind !== 'op') break;

            const topMeta = BINARY[top.value];
            const pops = topMeta.precedence > meta.precedence
                || (topMeta.precedence === meta.precedence && !meta.right);

            if (!pops) break;
            output.push(stack.pop());
        }

        stack.push(token);
    }

    while (stack.length > 0) {
        const top = stack.pop();

        requireDomain(top.kind !== 'lp');
        output.push(top);
    }

    return output;
}

function toRadians(x) {
    return state.angleMode === 'deg' ? (x * Math.PI) / 180 : x;
}

function fromRadians(x) {
    return state.angleMode === 'deg' ? (x * 180) / Math.PI : x;
}

function applyFunction(name, x) {
    switch (name) {
        case 'sin': requireDomain(Math.abs(x) < 1e15); return Math.sin(toRadians(x));
        case 'cos': requireDomain(Math.abs(x) < 1e15); return Math.cos(toRadians(x));
        case 'tan': {
            // 90° and 270° would silently return ~1e16 float noise; error instead.
            requireDomain(Math.abs(Math.cos(toRadians(x))) > 1e-12);
            return Math.tan(toRadians(x));
        }
        case 'asin': requireDomain(x >= -1 && x <= 1); return fromRadians(Math.asin(x));
        case 'acos': requireDomain(x >= -1 && x <= 1); return fromRadians(Math.acos(x));
        case 'atan': return fromRadians(Math.atan(x));
        case 'ln': requireDomain(x > 0); return Math.log(x);
        case 'log10': requireDomain(x > 0); return Math.log10(x);
        case 'exp': return Math.exp(x);
        case 'sqrt': requireDomain(x >= 0); return Math.sqrt(x);
        case 'cbrt': return Math.cbrt(x);
        default: throw new Error(`unknown function ${name}`);
    }
}

function factorial(n) {
    // Integer domain only; 170! is the largest double that stays finite.
    requireDomain(Number.isInteger(n) && n >= 0 && n <= 170);

    let product = 1;

    for (let k = 2; k <= n; k += 1) product *= k;

    return product;
}

/** Evaluate an RPN token list. */
function evaluateRpn(rpn) {
    const stack = [];

    const pop = () => {
        const value = stack.pop();

        if (value === undefined) throw new Error('incomplete');
        return value;
    };

    for (const token of rpn) {
        if (typeof token === 'number') {
            stack.push(token);
            continue;
        }

        if (token.kind === 'fact') {
            stack.push(factorial(pop()));
            continue;
        }

        if (token.kind === 'func') {
            stack.push(applyFunction(token.name, pop()));
            continue;
        }

        if (token.value === 'u') {
            stack.push(-pop());
            continue;
        }

        const b = pop();
        const a = pop();

        switch (token.value) {
            case '+': stack.push(a + b); break;
            case '-': stack.push(a - b); break;
            case '*': stack.push(a * b); break;
            case '/':
                if (b === 0) throw new Error('division by zero');
                stack.push(a / b);
                break;
            case '%':
                if (b === 0) throw new Error('division by zero');
                stack.push(a % b);
                break;
            case '^': stack.push(Math.pow(a, b)); break;
            default: throw new Error(`unknown operator ${token.value}`);
        }
    }

    if (stack.length !== 1) throw new Error('leftover values');

    return stack.pop();
}

/** Evaluate an expression string, or null if it is invalid/out of domain. */
function calculate(expression) {
    try {
        const result = evaluateRpn(toRpn(tokenize(expression)));

        if (!Number.isFinite(result)) throw new Error('not finite');

        // Round away floating point noise, but never overflow huge values.
        const rounded = Math.abs(result) < 1e290 ? Math.round(result * 1e10) / 1e10 : result;

        return String(rounded);
    } catch {
        return null;
    }
}

/* ---------------------------------------------------------------- display */

function render() {
    const text = state.error ? 'Error' : state.expression || '0';
    let previewText = '';

    if (state.error) {
        previewText = 'Invalid expression';
    } else if (state.expression !== '') {
        const result = calculate(state.expression);

        previewText = result !== null ? `= ${result}` : '';
    }

    panes.forEach((pane) => {
        pane.display.value = text;
        pane.preview.textContent = previewText;
    });
}

/** Reuse a finished result as a fresh expression (exponents included). */
function toExpression(value) {
    return /e[+-]\d+/.test(value) ? value.replace(/e([+-]\d+)/, '*10^($1)') : value;
}

function append(value) {
    if (state.error) state.expression = '';

    state.error = false;

    const isOperator = OPERATORS.includes(value);

    // Replace a trailing operator instead of stacking two in a row.
    if (isOperator && OPERATORS.includes(state.expression.slice(-1))) {
        state.expression = state.expression.slice(0, -1);
    }

    // Only one decimal point per number.
    if (value === '.') {
        const lastNumber = state.expression.split(/[+\-*/%^]/).pop();

        if (lastNumber.includes('.')) return;
    }

    state.expression += value;
    render();
}

function backspace() {
    // Delete multi-char insertions ("asin(", "^(-1)" ...) as one unit.
    const atomic = ATOMIC_TAILS.find((tail) => state.expression.endsWith(tail));

    state.expression = state.expression.slice(0, atomic ? -atomic.length : -1);
    state.error = false;
    render();
}

function clearAll() {
    state.expression = '';
    state.error = false;
    render();
}

function equals() {
    if (!state.expression || state.error) return;

    const result = calculate(state.expression);

    if (result === null) {
        state.error = true;
    } else {
        remember(state.expression, result);
        state.expression = toExpression(result);
    }

    render();
}

/* ---------------------------------------------------------------- history */

/** Read the persisted history, ignoring anything malformed. */
function loadHistory() {
    try {
        const stored = JSON.parse(readStored(STORAGE.history) ?? '[]');

        return Array.isArray(stored)
            ? stored.filter((entry) => typeof entry?.expression === 'string' && typeof entry?.result === 'string')
            : [];
    } catch {
        return [];
    }
}

function saveHistory() {
    writeStored(STORAGE.history, JSON.stringify(history));
}

function renderHistory() {
    if (!historyList) return;

    historyList.textContent = '';
    historyEmpty?.classList.toggle('hidden', history.length > 0);

    history.forEach((entry, index) => {
        const button = document.createElement('button');

        button.type = 'button';
        button.dataset.historyIndex = index;
        button.className = 'w-full cursor-pointer rounded-xl border border-slate-900/10 bg-white/50 px-3 py-2 text-right transition-colors hover:bg-white dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/10';

        const expression = document.createElement('span');

        expression.className = 'block truncate text-xs font-bold text-slate-400 dark:text-slate-500';
        expression.textContent = `${entry.expression} =`;

        const result = document.createElement('span');

        result.className = 'block truncate font-display text-lg font-semibold tabular-nums';
        result.textContent = entry.result;

        button.append(expression, result);

        const item = document.createElement('li');

        item.appendChild(button);
        historyList.appendChild(item);
    });
}

/** Record a finished calculation, keeping the newest entry first. */
function remember(expression, result) {
    history = [{ expression, result }, ...history.filter((entry) => entry.expression !== expression)]
        .slice(0, HISTORY_LIMIT);

    saveHistory();
    renderHistory();
}

/* -------------------------------------------------------------------- BMI */

/** Pure BMI maths, weight/height > 0 enforced; returns null when invalid. */
function computeBmi(weight, height, unit) {
    if (!(weight > 0) || !(height > 0)) return null;

    const factor = unit === 'metric' ? 10000 : 703;
    const bmi = (factor * weight) / (height * height);

    if (!Number.isFinite(bmi) || bmi <= 0) return null;

    const category = bmi < 18.5 ? 'Underweight' : bmi < 25 ? 'Normal' : bmi < 30 ? 'Overweight' : 'Obese';

    // BMI 18.5–24.9 solved back for weight in the same unit.
    const weightScale = unit === 'metric' ? (height * height) / 10000 : (height * height) / 703;

    return { bmi, category, low: 18.5 * weightScale, high: 24.9 * weightScale };
}

function applyBmiUnit() {
    document.querySelectorAll('#calculator [data-bmi-unit]').forEach((pill) => {
        pill.classList.toggle('is-active', pill.dataset.bmiUnit === state.bmiUnit);
    });

    document.querySelectorAll('#calculator [data-bmi-fields]').forEach((group) => {
        group.toggleAttribute('hidden', group.dataset.bmiFields !== state.bmiUnit);
    });
}

function renderBmi() {
    if (!bmiResultBox) return;

    const group = document.querySelector(`#calculator [data-bmi-fields="${state.bmiUnit}"]`);

    if (!group) return;

    const weightEl = group.querySelector('[data-bmi-role="weight"]');
    const heightEl = group.querySelector('[data-bmi-role="height"]');

    if (!weightEl.value && !heightEl.value) {
        bmiError.textContent = '';
        bmiResultBox.setAttribute('hidden', '');

        return;
    }

    const result = computeBmi(parseFloat(weightEl.value), parseFloat(heightEl.value), state.bmiUnit);

    if (!result) {
        bmiError.textContent = 'Enter a weight and a height greater than zero.';
        bmiResultBox.setAttribute('hidden', '');

        return;
    }

    bmiError.textContent = '';
    bmiResultBox.removeAttribute('hidden');

    bmiValueEl.textContent = result.bmi.toFixed(1);

    Object.values(BMI_TONES).forEach((classes) => bmiCategoryEl.classList.remove(...classes));
    bmiCategoryEl.classList.add(...BMI_TONES[result.category]);
    bmiCategoryEl.textContent = result.category;

    const unitLabel = state.bmiUnit === 'metric' ? 'kg' : 'lb';

    bmiRangeEl.textContent = `${result.low.toFixed(1)} – ${result.high.toFixed(1)} ${unitLabel}`;
}

/* ------------------------------------------------------------------- date */

function parseDateValue(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value ?? '');

    if (!match) return null;

    const date = { y: Number(match[1]), m: Number(match[2]), d: Number(match[3]) };
    const ms = Date.UTC(date.y, date.m - 1, date.d);

    // Reject impossible days (e.g. 2025-02-30 rolls over, UTC day must match).
    const check = new Date(ms);

    return check.getUTCFullYear() === date.y && check.getUTCMonth() === date.m - 1 && check.getUTCDate() === date.d
        ? date
        : null;
}

const DAY_MS = 86400000;
const toUtc = (date) => Date.UTC(date.y, date.m - 1, date.d);
const daysInMonth = (y, m) => new Date(Date.UTC(y, m, 0)).getUTCDate();

function addMonths(date, count) {
    const total = date.m - 1 + count;
    const y = date.y + Math.floor(total / 12);
    const m = (((total % 12) + 12) % 12) + 1;

    return { y, m, d: Math.min(date.d, daysInMonth(y, m)) };
}

/** Calendar difference between two YYYY-MM-DD strings, later minus earlier. */
function dateDifference(from, to) {
    const a = parseDateValue(from);
    const b = parseDateValue(to);

    if (!a || !b) return null;

    const ascending = toUtc(a) <= toUtc(b);
    const start = ascending ? a : b;
    const end = ascending ? b : a;

    // Greedy: largest whole-month shift whose (end-of-month clamped) result
    // still lands on or before the later date, remaining days counted plain.
    let months = (end.y - start.y) * 12 + (end.m - start.m);

    if (toUtc(addMonths(start, months)) > toUtc(end)) months -= 1;

    const shifted = addMonths(start, months);
    const totalDays = Math.round((toUtc(end) - toUtc(start)) / DAY_MS);

    return {
        start,
        end,
        swapped: !ascending,
        years: Math.floor(months / 12),
        months: months % 12,
        days: Math.round((toUtc(end) - toUtc(shifted)) / DAY_MS),
        totalDays,
        weeks: Math.floor(totalDays / 7),
        extraDays: totalDays % 7,
    };
}

const plural = (n, word) => `${n} ${word}${n === 1 ? '' : 's'}`;
const formatDate = (date) => `${date.d} ${MONTH_NAMES[date.m - 1]} ${date.y}`;

function renderDate() {
    if (!dateResultBox) return;

    const from = document.getElementById('date-from')?.value ?? '';
    const to = document.getElementById('date-to')?.value ?? '';

    if (!from || !to) {
        dateError.textContent = '';
        dateResultBox.setAttribute('hidden', '');

        return;
    }

    const diff = dateDifference(from, to);

    if (!diff) {
        dateError.textContent = 'Pick two valid dates.';
        dateResultBox.setAttribute('hidden', '');

        return;
    }

    dateError.textContent = '';
    dateResultBox.removeAttribute('hidden');

    dateNote.textContent = diff.totalDays === 0
        ? `Both fields point to the same day: ${formatDate(diff.start)}.`
        : `Earlier: ${formatDate(diff.start)} · Later: ${formatDate(diff.end)}${diff.swapped ? ' (your second date comes first)' : ''}`;

    dateYmd.textContent = diff.totalDays === 0
        ? '0 days'
        : `${plural(diff.years, 'year')}, ${plural(diff.months, 'month')}, ${plural(diff.days, 'day')}`;
    dateWeeks.textContent = `${plural(diff.weeks, 'week')}, ${plural(diff.extraDays, 'day')}`;
    dateDays.textContent = String(diff.totalDays);
}

/* ------------------------------------------------------------------- mode */

function isCalcMode() {
    return activeMode === 'standard' || activeMode === 'scientific';
}

function applyMode(mode) {
    activeMode = mode;

    // The history list only means something for the expression calculator.
    aside?.toggleAttribute('hidden', !isCalcMode());
}

function updateAngleToggle() {
    if (angleToggle) angleToggle.textContent = state.angleMode === 'deg' ? 'DEG' : 'RAD';
}

/* ----------------------------------------------------------------- wiring */

onReady(() => {
    const root = document.getElementById('calculator');

    if (!root) return;

    panes = [
        { display: document.getElementById('calc-expression'), preview: document.getElementById('calc-preview') },
        { display: document.getElementById('sci-expression'), preview: document.getElementById('sci-preview') },
    ].filter((pane) => pane.display && pane.preview);

    historyList = document.getElementById('calc-history');
    historyEmpty = document.getElementById('calc-history-empty');
    aside = document.getElementById('calc-history-aside');
    angleToggle = document.getElementById('calc-angle-toggle');
    bmiError = document.getElementById('bmi-error');
    bmiResultBox = document.getElementById('bmi-result');
    bmiValueEl = document.getElementById('bmi-value');
    bmiCategoryEl = document.getElementById('bmi-category');
    bmiRangeEl = document.getElementById('bmi-range');
    dateError = document.getElementById('date-error');
    dateResultBox = document.getElementById('date-result');
    dateNote = document.getElementById('date-note');
    dateYmd = document.getElementById('date-ymd');
    dateWeeks = document.getElementById('date-weeks');
    dateDays = document.getElementById('date-days');

    history = loadHistory();
    renderHistory();

    state.angleMode = readStored(STORAGE.angles) === 'rad' ? 'rad' : 'deg';
    updateAngleToggle();

    state.bmiUnit = readStored(STORAGE.bmiUnit) === 'imperial' ? 'imperial' : 'metric';
    applyBmiUnit();

    // Restore the last active mode (tabs.js has already rendered "standard"
    // as active, so we simply click the remembered trigger).
    const storedMode = readStored(STORAGE.mode);
    const trigger = storedMode ? root.querySelector(`[data-tab="${storedMode}"]`) : null;

    if (trigger && !trigger.classList.contains('is-active')) {
        if (root.dataset.tabsBound) {
            trigger.click();
        } else {
            // tabs.js has not bound yet — mark it active so its init picks it.
            root.querySelectorAll('[data-tab]').forEach((t) => t.classList.toggle('is-active', t === trigger));
        }
    }

    applyMode(trigger?.dataset.tab ?? 'standard');

    // Turbo morphs keep the root node, so bind its handlers only once.
    if (!root.dataset.bound) {
        root.dataset.bound = '1';

        root.addEventListener('click', (event) => {
            const tab = event.target.closest('[data-tab]');

            if (tab) {
                writeStored(STORAGE.mode, tab.dataset.tab);
                applyMode(tab.dataset.tab);

                return;
            }

            const unit = event.target.closest('[data-bmi-unit]');

            if (unit) {
                state.bmiUnit = unit.dataset.bmiUnit;
                writeStored(STORAGE.bmiUnit, state.bmiUnit);
                applyBmiUnit();
                renderBmi();

                return;
            }

            const angleButton = event.target.closest('[data-action="angle-toggle"]');

            if (angleButton) {
                state.angleMode = state.angleMode === 'deg' ? 'rad' : 'deg';
                writeStored(STORAGE.angles, state.angleMode);
                updateAngleToggle();
                render();
                angleButton.classList.add('scale-95');
                setTimeout(() => angleButton.classList.remove('scale-95'), 90);

                return;
            }

            if (event.target.closest('#calc-history-clear')) {
                history = [];
                saveHistory();
                renderHistory();

                return;
            }

            const entry = event.target.closest('[data-history-index]');

            if (entry) {
                const record = history[Number(entry.dataset.historyIndex)];

                if (record) {
                    state.expression = toExpression(record.result);
                    state.error = false;
                    render();
                }

                return;
            }

            const button = event.target.closest('[data-key]');

            if (!button) return;

            const key = button.dataset.key;

            switch (key) {
                case 'clear': clearAll(); break;
                case 'backspace': backspace(); break;
                case 'equals': equals(); break;
                default: append(key);
            }

            button.classList.add('scale-95');
            setTimeout(() => button.classList.remove('scale-95'), 90);
        });

        root.addEventListener('input', (event) => {
            if (event.target.closest('#bmi-panel')) renderBmi();
            else if (event.target.closest('#date-panel')) renderDate();
        });
    }

    // Keyboard support is bound once for the whole session; it only acts
    // while a calculator mode is showing and focus is not in a text field.
    if (!keyboardBound) {
        keyboardBound = true;

        document.addEventListener('keydown', (event) => {
            if (!document.getElementById('calculator') || !isCalcMode()) return;

            // Let the user type freely in BMI / date inputs.
            if (event.target instanceof HTMLInputElement && !event.target.readOnly) return;

            const { key } = event;

            if (/^[0-9.]$/.test(key) || key === '(' || key === ')' || key === '^' || key === '!') append(key);
            else if (OPERATORS.includes(key)) append(key);
            else if (key === 'Enter' || key === '=') equals();
            else if (key === 'Backspace') backspace();
            else if (key === 'Escape') clearAll();
            else return;

            event.preventDefault();
        });
    }

    renderBmi();
    renderDate();
    render();
});

export { calculate, computeBmi, dateDifference };
