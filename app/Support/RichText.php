<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Rich-text helpers for note bodies: a whitelist sanitiser plus plain-text
 * conversions. Built on strings/regex only because the PHP bundled with the
 * desktop app (nativephp/php) has no `dom` extension, so DOMDocument and
 * HTMLPurifier are both off the table here.
 */
class RichText
{
    /**
     * Tag whitelist: a, b, strong, i, em, u, s, strike, sub, sup, mark, p, br,
     * div, h1, h2, h3, ul, ol, li, blockquote, code, pre, hr, span.
     */
    private const ALLOWED_TAGS = [
        'a', 'b', 'strong', 'i', 'em', 'u', 's', 'strike', 'sub', 'sup', 'mark',
        'p', 'br', 'div', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote',
        'code', 'pre', 'hr', 'span',
    ];

    /** Elements removed together with everything inside them. */
    private const FORBIDDEN_ELEMENTS = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'svg', 'math',
        'link', 'meta', 'base', 'template', 'frame', 'frameset', 'applet',
    ];

    /** Only these may carry a `style` attribute, and only for text alignment. */
    private const ALIGNABLE_TAGS = [
        'p', 'div', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'pre', 'span',
    ];

    private const ALIGNMENTS = ['left', 'center', 'right', 'justify'];

    /** Schemes an `<a href>` may keep; anything relative is also fine. */
    private const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'ftp', 'ftps', 'sms'];

    private const DANGEROUS_SCHEMES = ['javascript', 'data', 'vbscript', 'file', 'about', 'blob', 'jar'];

    /** Tags that end a line in plain text: they become newlines in excerpts. */
    private const BLOCK_TAGS = [
        'p', 'div', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'pre', 'hr', 'br',
    ];

    /**
     * Reduce arbitrary HTML to the whitelist above: dangerous elements go with
     * their contents, unknown tags are stripped, unknown attributes dropped and
     * links are scheme-checked and forced to `rel="noopener noreferrer"`.
     */
    public static function sanitize(string $html): string
    {
        $html = str_replace("\0", '', $html);

        // (a) Comments and dangerous constructs, contents included.
        $html = preg_replace('~<!--.*?-->~s', '', $html);

        foreach (self::FORBIDDEN_ELEMENTS as $element) {
            $html = preg_replace('~<'.$element.'\b[^>]*>.*?</'.$element.'\s*>~is', '', $html);
        }

        // Comments such as `<!--[if]><script>` survive the paired pass; and a
        // never-closed tag would leave its opening bracket behind, so drop the
        // leftovers too.
        $html = str_replace(['<!--', '-->'], '', $html);

        foreach (self::FORBIDDEN_ELEMENTS as $element) {
            $html = preg_replace('~</?'.$element.'\b[^>]*>~i', '', $html);
        }

        // (b) Tag whitelist (inner text of removed tags is kept on purpose).
        $html = strip_tags($html, '<'.implode('><', self::ALLOWED_TAGS).'>');

        // (c) Attribute whitelist, plus (d) href schemes and (e) forced rel.
        $html = preg_replace_callback(
            '~<(/?)([a-z][a-z0-9]*)((?:\s[^<>]*)?)>~i',
            fn (array $m): string => self::renderTag(strtolower($m[2]), $m[1] !== '', $m[3]),
            $html
        );

        return trim((string) $html);
    }

    /**
     * Wrap blank-line-separated blocks in `<p>` and single newlines in `<br>`,
     * so notes written before the rich-text editor keep their paragraphing.
     */
    public static function fromPlainText(string $text): string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($text === '') {
            return '';
        }

        return implode("\n", array_map(
            fn (string $block): string => '<p>'.str_replace("\n", '<br>', htmlspecialchars($block, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')).'</p>',
            preg_split('~\n{2,}~', $text)
        ));
    }

    /**
     * Plain-text rendering of a body, for the card excerpts on the notes list.
     * Pass a `$limit` to truncate the way `Str::limit()` does.
     */
    public static function toPlainText(string $html, int $limit = 0): string
    {
        if (self::looksLikeHtml($html)) {
            $text = preg_replace(
                ['~<br\s*/?>~i',
                    '~</?(?:'.implode('|', self::BLOCK_TAGS).')(?:\s[^<>]*)?/?>~i',
                    '~</?[a-z][a-z0-9]*(?:\s[^<>]*)?/?>~i'],
                ["\n", "\n", ''],
                self::sanitize($html)
            );

            $text = html_entity_decode((string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } else {
            // Legacy plain-text rows were stored verbatim: do not decode them.
            $text = $html;
        }

        $text = preg_replace('~[ \t]++~', ' ', str_replace("\r", "\n", $text));
        $text = trim((string) preg_replace('~\n(?:[ \t]*\n)++~', "\n", $text));

        return $limit > 0 ? Str::limit($text, $limit) : $text;
    }

    /**
     * HTML for the on-screen reader view and for seeding the editor surface:
     * sanitises editor output and upgrades legacy plain-text rows so their line
     * breaks still show up as paragraphs.
     */
    public static function render(?string $body): string
    {
        $body = (string) $body;

        return self::looksLikeHtml($body) ? self::sanitize($body) : self::fromPlainText($body);
    }

    /**
     * Whether the value carries at least one whitelisted tag, i.e. it was
     * written by the editor rather than typed as plain text. Requires a real
     * open/close pair (or a standalone `br`/`hr`) so an old note whose text
     * merely compares values (`a < b`) is not mistaken for markup.
     */
    public static function looksLikeHtml(?string $value): bool
    {
        $value = (string) $value;

        if ($value === '' || ! Str::contains($value, '<')) {
            return false;
        }

        $paired = implode('|', array_diff(self::ALLOWED_TAGS, ['br', 'hr']));

        return preg_match('~<(?:br|hr)\s*/?>~i', $value) === 1
            || preg_match('~<('.$paired.')(?:\s[^<>]*)?>.*?</\1\s*>~is', $value) === 1;
    }

    /**
     * Rebuild one tag keeping only whitelisted attributes.
     */
    private static function renderTag(string $tag, bool $closing, string $rawAttributes): string
    {
        if ($closing) {
            return '</'.$tag.'>';
        }

        $attributes = [];

        if (! in_array($tag, ['br', 'hr'], true)) {
            foreach (self::parseAttributes($rawAttributes) as $name => $value) {
                if ($tag === 'a') {
                    if ($name === 'href') {
                        $href = self::safeHref($value);

                        if ($href !== null) {
                            $attributes['href'] = $href;
                        }
                    } elseif ($name === 'target') {
                        if (preg_match('~^_?(blank|self|parent|top)$~i', trim($value), $m)) {
                            $attributes['target'] = '_'.strtolower($m[1]);
                        }
                    } elseif ($name === 'rel') {
                        $tokens = preg_split('~[\s,]+~', strtolower(trim($value)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $tokens = array_intersect($tokens, ['noopener', 'noreferrer', 'nofollow']);

                        if ($tokens !== []) {
                            $attributes['rel'] = implode(' ', $tokens);
                        }
                    }
                } elseif ($name === 'style' && in_array($tag, self::ALIGNABLE_TAGS, true)) {
                    $alignment = self::textAlign($value);

                    if ($alignment !== null) {
                        $attributes['style'] = 'text-align:'.$alignment;
                    }
                }
            }
        }

        // (e) A link that opens a new tab must never hand the opener window
        // control over the new one.
        if (($attributes['target'] ?? null) === '_blank') {
            $extra = array_filter(
                preg_split('~[\s,]+~', $attributes['rel'] ?? ''),
                fn (?string $token): bool => ! in_array($token, ['', 'noopener', 'noreferrer'], true)
            );

            $attributes['rel'] = trim('noopener noreferrer '.implode(' ', $extra));
        }

        $rendered = '';

        foreach ($attributes as $name => $value) {
            $rendered .= ' '.$name.'="'.str_replace('"', '&quot;', $value).'"';
        }

        return '<'.$tag.$rendered.'>';
    }

    /**
     * Name => raw value pairs. Repeated attributes are discarded: browsers keep
     * the first while a naive parser keeps the last, which is how filters get
     * bypassed.
     */
    private static function parseAttributes(string $raw): array
    {
        $attributes = [];
        $seen = [];

        if (preg_match_all('~([a-z][a-z0-9._:-]*)\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'>]+))~i', $raw, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = strtolower($match[1]);
                $value = $match[2] !== '' ? $match[2] : ($match[3] !== '' ? $match[3] : ($match[4] ?? ''));

                if (in_array($name, $seen, true)) {
                    unset($attributes[$name]);

                    continue;
                }

                $seen[] = $name;
                $attributes[$name] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Keep a link target only when its scheme is safe.
     */
    private static function safeHref(string $href): ?string
    {
        // Decode entities and strip control characters/whitespace so tricks
        // like `java&#10;script:` or `jav\tascript:` cannot slip through.
        $probe = preg_replace('~[\x00-\x20\x7f]++~', '', html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $probe = strtolower(trim((string) $probe));

        if ($probe === '') {
            return null;
        }

        if (preg_match('~^('.implode('|', self::DANGEROUS_SCHEMES).')[:]~', $probe)) {
            return null;
        }

        if (preg_match('~^([a-z][a-z0-9+.\-]*):~', $probe, $m) && ! in_array($m[1], self::ALLOWED_SCHEMES, true)) {
            return null;
        }

        // Returned verbatim: it sits between quotes already, only `"` matters.
        return $href;
    }

    /**
     * The single alignment declaration an inline style is allowed to carry.
     */
    private static function textAlign(string $style): ?string
    {
        $compact = strtolower(preg_replace('~\s++~', '', $style));
        $compact = rtrim((string) $compact, ';');

        if (preg_match('~^text-align:('.implode('|', self::ALIGNMENTS).')$~', $compact, $m)) {
            return $m[1];
        }

        return null;
    }
}
