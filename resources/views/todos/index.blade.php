@extends('layouts.main')

@section('title', 'Todo Lists')

@section('content')
    <x-page-header>
        <x-slot:title>Todo Lists</x-slot:title>
        <x-slot:subtitle>
            {{ $todos->count() }} {{ str('list')->plural($todos->count()) }}
            @if ($hidden > 0) · {{ $hidden }} finished {{ str('list')->plural($hidden) }} hidden @endif
        </x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'todos']) }}" class="icon-btn" title="Todo settings">
                <i class="bi bi-gear"></i>
            </a>

            <a href="{{ route('todos.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i><span>New list</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($todos as $todo)
            @php($progress = $todo->progress())

            <a href="{{ route('todos.show', $todo) }}"
               class="glass glass-hover group flex flex-col gap-3 p-5 animate-fade-up"
               style="animation-delay: {{ ($loop->index % 9) * 60 }}ms">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="heading truncate text-lg leading-snug">{{ $todo->title }}</h2>

                    <span class="chip shrink-0 {{ $progress['percent'] === 100 ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' : 'bg-slate-900/10 text-slate-600 dark:bg-white/10 dark:text-slate-300' }}">
                        <i class="bi {{ $progress['percent'] === 100 ? 'bi-check-circle-fill' : 'bi-list-task' }}"></i>
                        {{ $progress['completed'] }}/{{ $progress['total'] }}
                    </span>
                </div>

                @if ($todo->description)
                    <p class="line-clamp-2 text-sm font-semibold leading-relaxed text-slate-600 dark:text-slate-400">
                        {{ $todo->description }}
                    </p>
                @endif

                <x-todo-meta :todo="$todo"/>

                <div class="mt-auto space-y-1.5 pt-2">
                    <div class="progress-track">
                        <span class="progress-fill block" style="width: {{ $progress['percent'] }}%"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs font-bold text-slate-400 dark:text-slate-500">
                        <span>{{ $progress['percent'] }}% complete</span>
                        <i class="bi bi-arrow-right opacity-0 transition-opacity group-hover:opacity-100"></i>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full py-6">
                <x-empty-state icon="bi-list-check" title="No todo lists yet">
                    <x-slot:description>Organise your day with a checklist.</x-slot:description>

                    <x-slot:action>
                        <a href="{{ route('todos.create') }}" class="btn-primary mt-2">
                            <i class="bi bi-plus-lg"></i>Create a list
                        </a>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @endforelse
    </div>
@endsection
