{{-- Shared todo-list editor form (expects $action, optional $todo and $items) --}}
@php([
    $todo = $todo ?? null,
    $items = $items ?? [],
])

<form id="todo-form" method="POST" action="{{ $action }}"
      class="flex flex-col gap-4 animate-fade-up">
    @csrf

    @if ($todo)
        @method('PUT')
    @endif

    <div>
        <input type="text" name="title" value="{{ old('title', $todo?->title) }}" required autocomplete="off"
               class="field-input !text-lg"
               placeholder="List title"/>

        @error('title')
            <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
        @enderror
    </div>

    <textarea name="description"
              class="field-input min-h-20 resize-y leading-relaxed"
              placeholder="Description (optional)">{{ old('description', $todo?->description) }}</textarea>

    {{-- Checklist items --}}
    <div>
        <div class="mb-2.5 flex items-center justify-between">
            <h3 class="heading text-base">Checklist items</h3>

            <button type="button" id="add-item" class="btn-ghost !px-3.5 !py-1.5 text-xs">
                <i class="bi bi-plus-lg"></i>Add row
            </button>
        </div>

        <ul id="todo-items" class="flex flex-col gap-2">
            @forelse (old('items', $items ?: [['text' => '', 'completed' => false]]) as $item)
                @include('todos.partial.item-row', ['text' => is_array($item) ? $item['text'] : $item])
            @empty
                @include('todos.partial.item-row', ['text' => ''])
            @endforelse
        </ul>

        @error('items.*')
            <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
        @enderror
    </div>

    {{-- Template for new rows, cloned by modules/todo-editor.js --}}
    <template id="item-template">
        @include('todos.partial.item-row', ['text' => ''])
    </template>
</form>
