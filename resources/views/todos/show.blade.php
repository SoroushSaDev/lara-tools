@extends('layouts.main')

@section('title', $todo->title)

@php($progress = $todo->progress())

@section('content')
    <div data-todo-page data-todo-id="{{ $todo->id }}">
        <x-page-header back="{{ route('todos.index') }}" back-label="Todo Lists">
            <x-slot:title>{{ str($todo->title)->limit(30) }}</x-slot:title>
            <x-slot:subtitle data-progress-text>{{ $progress['completed'] }}/{{ $progress['total'] }} done</x-slot:subtitle>

            <x-slot:actions>
                <a href="{{ route('todos.edit', $todo) }}" class="icon-btn hover:!bg-amber-400/20" title="Edit list">
                    <i class="bi bi-pencil"></i>
                </a>

                <form method="POST" action="{{ route('todos.destroy', $todo) }}">
                    @csrf
                    @method('DELETE')

                    <button type="submit" data-confirm="Move this list to the recycle bin?" class="icon-btn icon-btn-danger" title="Delete list">
                        <i class="bi bi-trash3"></i>
                    </button>
                </form>
            </x-slot:actions>
        </x-page-header>

        @if ($todo->description)
            <p class="mb-4 text-sm font-semibold leading-relaxed text-slate-600 animate-fade-up dark:text-slate-400">
                {{ $todo->description }}
            </p>
        @endif

        {{-- Overall progress --}}
        <div class="glass mb-5 p-4 animate-fade-up" style="animation-delay: 60ms">
            <div class="progress-track">
                <span class="progress-fill block" data-progress-bar style="width: {{ $progress['percent'] }}%"></span>
            </div>
        </div>

        {{-- Checklist --}}
        <ul class="flex flex-col gap-2.5">
            @forelse ($todo->items() as $index => $item)
                <li class="glass todo-item flex items-center gap-3.5 p-3.5 animate-fade-up"
                    style="animation-delay: {{ min(80 + $index * 40, 480) }}ms">
                    <input type="checkbox"
                           class="size-5 shrink-0 cursor-pointer accent-violet-600 transition-transform duration-200 hover:scale-110 dark:accent-fuchsia-500"
                           {{ $item['completed'] ? 'checked' : '' }}
                           data-toggle-url="{{ route('todos.items.toggle', [$todo, $index]) }}"/>

                    <p data-item-text
                       class="min-w-0 flex-1 break-words transition-all duration-300 {{ $item['completed'] ? 'todo-item-done' : '' }}">
                        {{ $item['text'] }}
                    </p>

                    @if ($item['completed'])
                        <i class="bi bi-check-circle-fill shrink-0 text-emerald-500"></i>
                    @endif
                </li>
            @empty
                <li class="py-6">
                    <x-empty-state icon="bi-ui-checks" title="Empty list">
                        <x-slot:description>Edit the list to add checklist items.</x-slot:description>

                        <x-slot:action>
                            <a href="{{ route('todos.edit', $todo) }}" class="btn-primary mt-2">
                                <i class="bi bi-pencil"></i>Add items
                            </a>
                        </x-slot:action>
                    </x-empty-state>
                </li>
            @endforelse
        </ul>
    </div>
@endsection
