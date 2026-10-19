/**
 * Rich-text editor for the note body (resources/views/notes/partial/form.blade.php).
 *
 * Contract:
 *  - Root:   [data-editor] wrapping a .rt-toolbar of [data-cmd] buttons and
 *            a [data-editor-input] (contenteditable) + hidden input[name=body]
 *  - The hidden input always mirrors the sanitised HTML so the form posts it.
 */

import { onReady } from '../support/ready';

// Mirrors the server-side whitelist in App\Support\RichText::sanitize(); the
// paste handler and the hidden input use these to keep the editing view tidy.
const ALLOWED_TAGS = new Set([
    'a', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup', 'mark',
    'p', 'br', 'div', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote',
    'code', 'pre', 'hr', 'span',
]);

// Elements removed outright (contents and all) from pasted HTML.
const FORBIDDEN_TAGS = new Set([
    'script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math',
    'link', 'meta', 'base', 'template', 'frame', 'frameset', 'applet',
]);

const ALIGNABLE_TAGS = new Set(['p', 'div', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'pre', 'span']);
const ALIGN_STYLE = /^text-align:\s*(left|center|right|justify)\s*;?$/i;
const SAFE_HREF = /^(?:https?:|mailto:|tel:|ftp:|ftps:|sms:|#|\/|\.)/i;
const DANGEROUS_HREF = /^(?:javascript|data|vbscript|file|about|blob|jar):/i;

// Commands whose active state Chromium reports through queryCommandState.
const TOGGLE_COMMANDS = new Set([
    'bold', 'italic', 'underline', 'strikeThrough',
    'insertUnorderedList', 'insertOrderedList',
    'justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull',
]);

// `selectionchange` only fires on the document, so a single module-level
// listener forwards to whichever editor instance is on the current page. The
// holder is cleared on every Turbo visit so a detached DOM is never polled.
let activeEditor = null;

document.addEventListener('selectionchange', () => activeEditor?.());

const escapeHtml = (value) => value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

const escapeAttribute = (value) => escapeHtml(value).replace(/"/g, '&quot;');

onReady(() => {
    activeEditor = null;

    document.querySelectorAll('[data-editor]').forEach((root) => {
        if (root.dataset.editorBound) return;

        const toolbar = root.querySelector('.rt-toolbar');
        const input = root.querySelector('[data-editor-input]');
        const hidden = root.querySelector('[data-editor-value]');
        const form = root.closest('form');
        const bodyError = root.querySelector('[data-body-error]');
        const linkField = root.querySelector('[data-link-field]');
        const linkInput = root.querySelector('[data-link-url]');
        const linkError = root.querySelector('[data-link-error]');

        if (!toolbar || !input || !hidden || !form) return;

        root.dataset.editorBound = '1';

        try {
            document.execCommand('defaultParagraphSeparator', false, 'p');
        } catch {
            // Older/newer engines only: Enter still produces a block either way.
        }

        let savedRange = null;

        // ---- helpers -------------------------------------------------------

        const selection = () => window.getSelection();

        const selectionIsInEditor = () => {
            const sel = selection();

            return Boolean(sel) && sel.rangeCount > 0 && input.contains(sel.anchorNode);
        };

        const tagInSelection = (tagName) => {
            if (!selectionIsInEditor()) return null;

            let node = selection().getRangeAt(0).commonAncestorContainer;

            if (node.nodeType === Node.TEXT_NODE) node = node.parentNode;

            return node?.closest?.(tagName.toLowerCase()) ?? null;
        };

        const captureRange = () => {
            if (!selectionIsInEditor()) return null;

            return selection().getRangeAt(0).cloneRange();
        };

        const restoreRange = (range) => {
            if (!range) return;

            const sel = selection();

            sel.removeAllRanges();
            sel.addRange(range);
        };

        // Toolbar clicks must not take the caret away from the editor.
        const ensureSelection = () => {
            input.focus();

            if (selectionIsInEditor()) return;

            const range = document.createRange();

            range.selectNodeContents(input);
            range.collapse(false);
            restoreRange(range);
        };

        const isEmpty = () => input.textContent.replace(/[\s\u00a0\u200b]+/g, '') === ''
            && !input.querySelector('hr');

        // The single source of truth for what gets posted.
        const sync = () => {
            const empty = isEmpty();

            hidden.value = empty ? '' : input.innerHTML;
            input.dataset.empty = empty ? 'true' : 'false';

            if (!empty && bodyError) {
                bodyError.hidden = true;
            }
        };

        const refreshToolbar = () => {
            const inside = selectionIsInEditor();
            const block = inside ? (document.queryCommandValue('formatBlock') || '').toLowerCase() : '';

            toolbar.querySelectorAll('[data-cmd]').forEach((button) => {
                const cmd = button.dataset.cmd;
                let active = false;

                try {
                    if (cmd === 'formatBlock') {
                        active = inside && block === (button.dataset.value || '').toLowerCase();
                    } else if (cmd === 'inlineCode') {
                        active = Boolean(tagInSelection('code'));
                    } else if (cmd === 'link' || cmd === 'unlink') {
                        active = Boolean(tagInSelection('a'));
                    } else if (TOGGLE_COMMANDS.has(cmd)) {
                        active = inside && document.queryCommandState(cmd);
                    }
                } catch {
                    active = false;
                }

                button.classList.toggle('is-active', active);
            });
        };

        // ---- commands ------------------------------------------------------

        // There is no execCommand for inline code, so wrap (or unwrap) by hand.
        const toggleInlineCode = () => {
            const code = tagInSelection('code');

            if (code && input.contains(code)) {
                while (code.firstChild) code.parentNode.insertBefore(code.firstChild, code);
                code.remove();
                input.normalize();

                return;
            }

            if (selection().toString() === '') selectWord();

            const text = selection().toString();

            document.execCommand('insertHTML', false, text === ''
                ? '<code>&nbsp;</code>'
                : `<code>${escapeHtml(text)}</code>`);
        };

        // Wrap the word the caret is sitting on, so the inline tools also work
        // without a selection.
        const selectWord = () => {
            const sel = selection();
            const node = sel.anchorNode;

            if (!node || node.nodeType !== Node.TEXT_NODE) return;

            const text = node.nodeValue;
            let start = sel.anchorOffset;
            let end = sel.anchorOffset;

            while (start > 0 && !/\s/.test(text[start - 1])) start -= 1;
            while (end < text.length && !/\s/.test(text[end])) end += 1;

            if (start === end) return;

            const range = document.createRange();

            range.setStart(node, start);
            range.setEnd(node, end);
            restoreRange(range);
        };

        const clearFormatting = () => {
            document.execCommand('removeFormat');

            const code = tagInSelection('code');

            if (code && input.contains(code)) {
                while (code.firstChild) code.parentNode.insertBefore(code.firstChild, code);
                code.remove();
            }

            const block = (document.queryCommandValue('formatBlock') || '').toLowerCase();

            if (['h1', 'h2', 'h3'].includes(block)) {
                document.execCommand('formatBlock', false, 'p');
            }
        };

        // Formatting runs through document.execCommand: deprecated on the open
        // web, but this app only ever runs in the bundled Chromium webview where
        // it stays fully supported, and it remains the only pragmatic way to get
        // selection-safe block/inline formatting plus a native undo stack.
        const runCommand = (cmd, value) => {
            ensureSelection();

            switch (cmd) {
                case 'inlineCode':
                    toggleInlineCode();
                    break;
                case 'link':
                    openLinkField();
                    return;
                case 'unlink':
                    if (tagInSelection('a')) document.execCommand('unlink');
                    break;
                case 'removeFormat':
                    clearFormatting();
                    break;
                case 'formatBlock':
                    document.execCommand('formatBlock', false, value || 'p');
                    break;
                default:
                    document.execCommand(cmd, false, value ?? null);
            }

            sync();
            refreshToolbar();
        };

        // ---- link field ----------------------------------------------------

        function openLinkField() {
            if (!linkField || !linkInput) return;

            savedRange = captureRange();

            const anchor = tagInSelection('a');

            linkInput.value = anchor ? anchor.getAttribute('href') || '' : '';
            linkField.hidden = false;

            if (linkError) linkError.hidden = true;

            linkInput.focus();
            linkInput.select();
        }

        function closeLinkField() {
            if (!linkField) return;

            linkField.hidden = true;
            restoreRange(savedRange);
            refreshToolbar();
        }

        function applyLink() {
            let url = linkInput.value.trim();

            if (url === '') {
                closeLinkField();

                return;
            }

            if (!/^[a-z][a-z0-9+.-]*:/i.test(url) && !/^[#/.\s]/.test(url)) {
                url = `https://${url}`;
            }

            if (DANGEROUS_HREF.test(url) || !SAFE_HREF.test(url)) {
                if (linkError) linkError.hidden = false;
                linkInput.focus();

                return;
            }

            if (linkError) linkError.hidden = true;

            linkField.hidden = true;
            restoreRange(savedRange);
            ensureSelection();

            const anchor = tagInSelection('a');

            if (selection().isCollapsed && anchor) {
                anchor.setAttribute('href', url);
                anchor.setAttribute('target', '_blank');
                anchor.setAttribute('rel', 'noopener noreferrer');
            } else if (selection().isCollapsed) {
                document.execCommand('insertHTML', false,
                    `<a href="${escapeAttribute(url)}" target="_blank" rel="noopener noreferrer">${escapeHtml(url)}</a>`);
            } else {
                document.execCommand('createLink', false, url);

                const created = tagInSelection('a');

                if (created) {
                    created.setAttribute('target', '_blank');
                    created.setAttribute('rel', 'noopener noreferrer');
                }
            }

            linkInput.value = '';
            sync();
            refreshToolbar();
        }

        function removeLink() {
            linkField.hidden = true;
            linkInput.value = '';

            if (linkError) linkError.hidden = true;

            restoreRange(savedRange);
            ensureSelection();

            if (tagInSelection('a')) document.execCommand('unlink');

            sync();
            refreshToolbar();
        }

        // ---- wiring --------------------------------------------------------

        toolbar.addEventListener('mousedown', (event) => {
            if (event.target.closest('input')) return;

            event.preventDefault();
        });

        toolbar.addEventListener('click', (event) => {
            const button = event.target.closest('[data-cmd]');

            if (button) {
                runCommand(button.dataset.cmd, button.dataset.value);

                return;
            }

            if (event.target.closest('[data-link-apply]')) applyLink();
            if (event.target.closest('[data-link-remove]')) removeLink();
            if (event.target.closest('[data-link-cancel]')) closeLinkField();
        });

        linkInput?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyLink();
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeLinkField();
            }
        });

        input.addEventListener('input', sync);
        input.addEventListener('change', sync);

        input.addEventListener('paste', (event) => {
            event.preventDefault();

            const transfer = event.clipboardData;
            const html = transfer?.getData('text/html');

            if (html) {
                document.execCommand('insertHTML', false, cleanPaste(html));
            } else {
                document.execCommand('insertText', false, transfer?.getData('text/plain') ?? '');
            }

            sync();
        });

        input.addEventListener('keydown', (event) => {
            const mod = event.ctrlKey || event.metaKey;

            if (mod && !event.altKey) {
                const shortcuts = { b: 'bold', i: 'italic', u: 'underline', k: 'link' };
                const cmd = shortcuts[event.key.toLowerCase()];

                if (cmd) {
                    event.preventDefault();
                    runCommand(cmd);

                    return;
                }
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                document.execCommand(event.shiftKey ? 'insertLineBreak' : 'insertParagraph');
                sync();

                return;
            }

            if (event.key === 'Escape' && linkField && !linkField.hidden) {
                closeLinkField();
            }
        });

        // The header Save button calls requestSubmit(), which still dispatches
        // a submit event — this catches both paths before the values are read.
        form.addEventListener('submit', (event) => {
            sync();

            if (hidden.value.trim() !== '') return;

            event.preventDefault();

            if (bodyError) bodyError.hidden = false;

            ensureSelection();
        });

        sync();
        refreshToolbar();

        // This instance owns the module-level selectionchange forwarder.
        activeEditor = refreshToolbar;
    });
});

/**
 * Best-effort client mirror of App\Support\RichText::sanitize(). The server
 * re-sanitises on save; this only keeps junk styles/scripts out of the view
 * while the user is still editing.
 */
function cleanPaste(html) {
    const body = new DOMParser().parseFromString(html, 'text/html').body;

    cleanse(body);

    return body.innerHTML;
}

function cleanse(container) {
    [...container.childNodes].forEach((node) => {
        if (node.nodeType === Node.TEXT_NODE) return;

        if (node.nodeType !== Node.ELEMENT_NODE) {
            node.remove();

            return;
        }

        const tag = node.tagName.toLowerCase();

        if (FORBIDDEN_TAGS.has(tag)) {
            node.remove();

            return;
        }

        [...node.attributes].forEach((attribute) => {
            const name = attribute.name.toLowerCase();
            const allowed = (tag === 'a' && ['href', 'target', 'rel'].includes(name))
                || (name === 'style' && ALIGNABLE_TAGS.has(tag) && ALIGN_STYLE.test(attribute.value));

            if (!allowed) node.removeAttribute(attribute.name);
        });

        if (tag === 'a' && !SAFE_HREF.test(node.getAttribute('href') ?? '')) {
            node.removeAttribute('href');
        }

        if (!ALLOWED_TAGS.has(tag)) {
            cleanse(node);

            while (node.firstChild) container.insertBefore(node.firstChild, node);
            node.remove();

            return;
        }

        cleanse(node);
    });
}
