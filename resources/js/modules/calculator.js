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
 */

import { onReady } from '../support/ready';

const OPERATORS = {
    '+': { precedence: 1 },
    '-': { precedence: 1 },
    '*': { precedence: 2 },
    '/': { precedence: 2 },
    '%': { precedence: 2 },
};

const state = {
    expression: '',
    error: false,
};

let display = null;
let preview = null;
let keyboardBound = false;

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

    const result = state.error || state.expression === '' ? '' : calculate(state.expression);

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
        preview.textContent = 'Invalid expression';
    } else {
        state.expression = result;
    }

    render();
}

onReady(() => {
    const root = document.getElementById('calculator');

    if (!root) return;

    display = document.getElementById('calc-expression');
    preview = document.getElementById('calc-preview');

    root.addEventListener('click', (event) => {
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
