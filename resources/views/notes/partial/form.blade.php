@php
    use App\Support\RichText;

    $note = $note ?? null;

    // Legacy rows hold plain text: rebuilding them as paragraphs keeps the
    // paragraphing the user already wrote visible inside the editor.
    $body = RichText::render(old('body', $note?->body));

    // Each group is one toolbar cluster; `value` is the formatBlock tag name.
    $groups = [
        [
            ['cmd' => 'undo', 'icon' => 'arrow-counterclockwise', 'label' => 'Undo'],
            ['cmd' => 'redo', 'icon' => 'arrow-clockwise', 'label' => 'Redo'],
        ],
        [
            ['cmd' => 'formatBlock', 'value' => 'p', 'icon' => 'paragraph', 'label' => 'Paragraph'],
            ['cmd' => 'formatBlock', 'value' => 'h1', 'icon' => 'type-h1', 'label' => 'Heading 1'],
            ['cmd' => 'formatBlock', 'value' => 'h2', 'icon' => 'type-h2', 'label' => 'Heading 2'],
            ['cmd' => 'formatBlock', 'value' => 'h3', 'icon' => 'type-h3', 'label' => 'Heading 3'],
        ],
        [
            ['cmd' => 'bold', 'icon' => 'type-bold', 'label' => 'Bold (Ctrl+B)'],
            ['cmd' => 'italic', 'icon' => 'type-italic', 'label' => 'Italic (Ctrl+I)'],
            ['cmd' => 'underline', 'icon' => 'type-underline', 'label' => 'Underline (Ctrl+U)'],
            ['cmd' => 'strikeThrough', 'icon' => 'type-strikethrough', 'label' => 'Strikethrough'],
            ['cmd' => 'inlineCode', 'icon' => 'code-slash', 'label' => 'Inline code'],
        ],
        [
            ['cmd' => 'insertUnorderedList', 'icon' => 'list-ul', 'label' => 'Bullet list'],
            ['cmd' => 'insertOrderedList', 'icon' => 'list-ol', 'label' => 'Numbered list'],
            ['cmd' => 'formatBlock', 'value' => 'blockquote', 'icon' => 'blockquote-left', 'label' => 'Quote'],
        ],
        [
            ['cmd' => 'justifyLeft', 'icon' => 'text-left', 'label' => 'Align left'],
            ['cmd' => 'justifyCenter', 'icon' => 'text-center', 'label' => 'Align centre'],
            ['cmd' => 'justifyRight', 'icon' => 'text-right', 'label' => 'Align right'],
            ['cmd' => 'justifyFull', 'icon' => 'justify', 'label' => 'Justify'],
        ],
        [
            ['cmd' => 'link', 'icon' => 'link', 'label' => 'Link (Ctrl+K)'],
            ['cmd' => 'unlink', 'icon' => 'link-45deg', 'label' => 'Unlink'],
            ['cmd' => 'insertHorizontalRule', 'icon' => 'hr', 'label' => 'Divider'],
            ['cmd' => 'removeFormat', 'icon' => 'eraser', 'label' => 'Clear formatting'],
        ],
    ];

    // Compact toolbar: drop the heading (1) and alignment (4) clusters,
    // leaving inline styles, lists, quotes and links.
    if ($notesToolbarCompact) {
        $groups = array_values(array_filter(
            $groups,
            fn (array $group, int $index): bool => ! in_array($index, [1, 4], true),
            ARRAY_FILTER_USE_BOTH
        ));
    }
@endphp

{{-- Shared note editor form (expects $action and optional $note) --}}
<form id="note-form" method="POST" action="{{ $action }}"
      class="flex h-[max(22rem,calc(100vh-20rem))] flex-col gap-3 animate-fade-up">
    @csrf

    @if ($note)
        @method('PUT')
    @endif

    <div>
        <input type="text" name="title" value="{{ old('title', $note?->title) }}" autocomplete="off"
               class="field-input !text-lg"
               placeholder="Title (optional)"/>

        @error('title')
            <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
        @enderror
    </div>

    <div data-editor class="flex min-h-0 flex-1 flex-col">
        <div class="rt-toolbar" role="toolbar" aria-label="Text formatting">
            @foreach ($groups as $group)
                @unless ($loop->first)
                    <span class="rt-sep" aria-hidden="true"></span>
                @endunless

                @foreach ($group as $tool)
                    <button type="button" class="rt-btn"
                            data-cmd="{{ $tool['cmd'] }}"
                            @if (isset($tool['value'])) data-value="{{ $tool['value'] }}" @endif
                            title="{{ $tool['label'] }}" aria-label="{{ $tool['label'] }}">
                        <i class="bi bi-{{ $tool['icon'] }}" aria-hidden="true"></i>
                    </button>
                @endforeach
            @endforeach

            {{-- Link tool: an inline field instead of prompt(), so the app keeps
                 its own chrome and the caret survives while typing. --}}
            <div class="flex w-full flex-wrap items-center gap-1 pt-1" data-link-field hidden>
                <input type="text" data-link-url class="field-input min-w-0 flex-1 !py-2 !text-sm"
                       placeholder="https://example.com" autocomplete="off" spellcheck="false"
                       aria-label="Link address"/>

                <button type="button" class="rt-btn" data-link-apply title="Apply link" aria-label="Apply link">
                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                </button>

                <button type="button" class="rt-btn" data-link-remove title="Remove link" aria-label="Remove link">
                    <i class="bi bi-link-45deg" aria-hidden="true"></i>
                </button>

                <button type="button" class="rt-btn" data-link-cancel title="Close link field" aria-label="Close link field">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>

                <span class="field-error !mt-0 basis-full" data-link-error hidden>Use an http(s), mailto or tel address.</span>
            </div>
        </div>

        <div data-editor-input contenteditable="true" role="textbox" aria-multiline="true" aria-label="Note body"
             class="rt-body flex-1 min-h-0 overflow-y-auto rounded-b-2xl border border-slate-900/10 bg-white/70 p-4 dark:border-white/10 dark:bg-white/[0.07]"
             data-placeholder="Start writing your note..."
             data-empty="{{ $body === '' ? 'true' : 'false' }}">{!! $body !!}</div>

        {{-- The writable surface is not a form control, so this mirror is what
             actually gets posted. --}}
        <input type="hidden" name="body" value="{{ $body }}" data-editor-value/>

        @error('body')
            <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
        @enderror

        <span class="field-error" data-body-error hidden>
            <i class="bi bi-exclamation-circle me-1"></i>Write something before saving.
        </span>
    </div>
</form>
