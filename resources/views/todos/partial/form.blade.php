{{-- Shared todo-list editor form (expects $action, optional $todo and $items) --}}
@php([
    $todo = $todo ?? null,
    $items = $items ?? [],
    $priority = old('priority', $todo?->priority ?? 'none'),
    $priorityTones = [
        'none' => 'peer-checked:border-slate-400/70 peer-checked:bg-slate-500/15 peer-checked:text-slate-800 dark:peer-checked:text-slate-100',
        'low' => 'peer-checked:border-sky-500 peer-checked:bg-sky-500/15 peer-checked:text-sky-700 dark:peer-checked:text-sky-300',
        'medium' => 'peer-checked:border-amber-500 peer-checked:bg-amber-500/15 peer-checked:text-amber-700 dark:peer-checked:text-amber-300',
        'high' => 'peer-checked:border-rose-500 peer-checked:bg-rose-500/15 peer-checked:text-rose-700 dark:peer-checked:text-rose-300',
    ],
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

    {{-- Deadline & priority --}}
    <div class="glass grid gap-4 p-4 sm:grid-cols-2">
        <div>
            <label for="due_at" class="mb-2 block text-sm font-bold">Due date</label>

            <input type="date" id="due_at" name="due_at"
                   value="{{ old('due_at', $todo?->due_at?->format('Y-m-d')) }}"
                   class="field-input"/>

            @error('due_at')
                <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
            @enderror
        </div>

        <div>
            <span class="mb-2 block text-sm font-bold">Priority</span>

            <div class="flex flex-wrap gap-2" role="radiogroup" aria-label="Priority">
                @foreach (['none' => 'None', 'low' => 'Low', 'medium' => 'Medium', 'high' => 'High'] as $value => $option)
                    <label class="cursor-pointer">
                        <input type="radio" name="priority" value="{{ $value }}"
                               class="peer sr-only" @checked($priority === $value)/>

                        <span class="block rounded-full border-2 border-slate-900/10 bg-white/50 px-4 py-1.5 text-xs font-bold transition-all
                            hover:bg-white {{ $priorityTones[$value] }}
                            dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/10">
                            {{ $option }}
                        </span>
                    </label>
                @endforeach
            </div>

            @error('priority')
                <span class="field-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span>
            @enderror
        </div>
    </div>

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
