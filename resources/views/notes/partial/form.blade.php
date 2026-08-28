{{-- Shared note editor form (expects $action and optional $note) --}}
@php($note = $note ?? null)

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

    <div class="flex min-h-0 flex-1 flex-col">
        <textarea name="body" required
                  class="field-input h-full flex-1 resize-none leading-relaxed"
                  placeholder="Start writing your note...">{{ old('body', $note?->body) }}</textarea>

        @error('body')
            <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
        @enderror
    </div>
</form>
