/**
 * Calculator engine.
 *
 * Implements a small tokenizer + shunting-yard evaluator so expressions are
 * evaluated with correct operator precedence — no eval(), no surprises.
 * Supported: + - × ÷ % (modulo), decimals, unary minus.
 *
 * Contract with the Blade view:
 *  - Root:        #calculator
 *  - Expression:  #calc-expression (readonly input)
 *  - Live result: #calc-preview
 *  - Buttons:     [data-key="0".."9" | "." | "+" | "-" | "*" | "/" | "%" |
 *                  "clear" | "backspace" | "equals"]
 *  - History:     #calc-history list fed by localStorage, #calc-history-clear
 */

import { onReady } from '../support/ready';

const OPERATORS = {
    '+': { precedence: 1 },
    '-': { precedence: 1 },
    '*': { precedence: 2 },
    '/': { precedence: 2 },
    '%': { precedence: 2 },
};

const HISTORY_KEY = 'lara-tools.calc-history';
const HISTORY_LIMIT = 30;

const state = {
    expression: '',
    error: false,
};

let display = null;
let preview = null;
let keyboardBound = false;
let history = [];
let historyList = null;
let historyEmpty = null;

/** Read the persisted history, ignoring anything malformed. */
function loadHistory() {
    try {
        const stored = JSON.parse(localStorage.getItem(HISTORY_KEY) ?? '[]');

        return Array.isArray(stored)
            ? stored.filter((entry) => typeof entry?.expression === 'string' && typeof entry?.result === 'string')
            : [];
    } catch {
        return [];
    }
}

function saveHistory() {
    try {
        localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
    } catch {
        // Storage unavailable (private mode / quota); history stays in memory.
    }
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

/** Split the raw expression into number and operator tokens. */
function tokenize(input) {
    const tokens = [];
    let buffer = '';

    for (const char of input) {
        if (char in OPERATORS) {
            if (buffer !== '') {
                tokens.push(buffer);
                buffer = '';
            }

            // Unary minus: at the start or right after another operator.
            const previous = tokens[tokens.length - 1];

            if (char === '-' && (previous === undefined || previous in OPERATORS)) {
                buffer = '-';
                continue;
            }

            tokens.push(char);
            continue;
        }

        buffer += char;
    }

    if (buffer !== '') tokens.push(buffer);

    return tokens;
}

/** Convert infix tokens to reverse-polish notation. */
function toRpn(tokens) {
    const output = [];
    const stack = [];

    for (const token of tokens) {
        if (token in OPERATORS) {
            while (
                stack.length > 0
                && stack[stack.length - 1] in OPERATORS
                && OPERATORS[stack[stack.length - 1]].precedence >= OPERATORS[token].precedence
            ) {
                output.push(stack.pop());
            }

            stack.push(token);
            continue;
        }

        const number = Number(token);

        if (!Number.isFinite(number)) throw new Error('bad token');
        output.push(number);
    }

    while (stack.length > 0) output.push(stack.pop());

    return output;
}

/** Evaluate an RPN token list. */
function evaluateRpn(rpn) {
    const stack = [];

    for (const token of rpn) {
        if (typeof token === 'number') {
            stack.push(token);
            continue;
        }

        const b = stack.pop();
        const a = stack.pop();

        if (a === undefined || b === undefined) throw new Error('incomplete');

        switch (token) {
            case '+': stack.push(a + b); break;
            case '-': stack.push(a - b); break;
            case '*': stack.push(a * b); break;
            case '/':
                if (b === 0) throw new Error('division by zero');
                stack.push(a / b);
                break;
            case '%': stack.push(a % b); break;
            default: throw new Error(`unknown operator ${token}`);
        }
    }

    if (stack.length !== 1) throw new Error('leftover values');

    return stack.pop();
}

function calculate(expression) {
    try {
        const result = evaluateRpn(toRpn(tokenize(expression)));

        if (!Number.isFinite(result)) throw new Error('not finite');

        // Round away floating point noise for tidy output.
        return String(Math.round(result * 1e10) / 1e10);
    } catch {
        return null;
    }
}

function render() {
    display.value = state.error ? 'Error' : state.expression || '0';

    if (state.error) {
        preview.textContent = 'Invalid expression';

        return;
    }

    const result = state.expression === '' ? '' : calculate(state.expression);

    preview.textContent = result !== null && result !== '' ? `= ${result}` : '';
}

function append(value) {
    if (state.error) state.expression = '';

    state.error = false;

    const isOperator = value in OPERATORS;

    // Replace a trailing operator instead of stacking two in a row.
    if (isOperator && state.expression.slice(-1) in OPERATORS) {
        state.expression = state.expression.slice(0, -1);
    }

    // Only one decimal point per number.
    if (value === '.') {
        const lastNumber = state.expression.split(/[+\-*/%]/).pop();

        if (lastNumber.includes('.')) return;
    }

    state.expression += value;
    render();
}

function backspace() {
    state.expression = state.expression.slice(0, -1);
    state.error = false;
    render();
}

function clear() {
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
        state.expression = result;
    }

    render();
}

onReady(() => {
    const root = document.getElementById('calculator');

    if (!root) return;

    display = document.getElementById('calc-expression');
    preview = document.getElementById('calc-preview');
    historyList = document.getElementById('calc-history');
    historyEmpty = document.getElementById('calc-history-empty');
    history = loadHistory();

    renderHistory();

    // Turbo morphs keep the root node, so bind its handler only once.
    if (!root.dataset.bound) {
        root.dataset.bound = '1';

        root.addEventListener('click', (event) => {
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
                    state.expression = record.result;
                    state.error = false;
                    render();
                }

                return;
            }

            const button = event.target.closest('[data-key]');

            if (!button) return;

            const key = button.dataset.key;

            switch (key) {
                case 'clear': clear(); break;
                case 'backspace': backspace(); break;
                case 'equals': equals(); break;
                default: append(key);
            }

            button.classList.add('scale-95');
            setTimeout(() => button.classList.remove('scale-95'), 90);
        });
    }

    // Keyboard support is bound once for the whole session; it only acts
    // while the calculator page is on screen.
    if (!keyboardBound) {
        keyboardBound = true;

        document.addEventListener('keydown', (event) => {
            if (!document.getElementById('calculator')) return;

            const { key } = event;

            if (/^[0-9.]$/.test(key)) append(key);
            else if (key in OPERATORS) append(key);
            else if (key === 'Enter' || key === '=') equals();
            else if (key === 'Backspace') backspace();
            else if (key === 'Escape') clear();
            else return;

            event.preventDefault();
        });
    }

    render();
});
