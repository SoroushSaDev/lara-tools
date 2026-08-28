@extends('layouts.main')

@section('title', 'Notes')

@section('content')
    <x-page-header>
        <x-slot:title>Notes</x-slot:title>
        <x-slot:subtitle>{{ $notes->count() }} saved</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'notes']) }}" class="icon-btn" title="Notes settings">
                <i class="bi bi-gear"></i>
            </a>

            <a href="{{ route('notes.create') }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i><span>New note</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($notes as $note)
            <a href="{{ route('notes.show', $note) }}"
               class="glass glass-hover group relative flex flex-col gap-3 p-5 animate-fade-up"
               style="animation-delay: {{ ($loop->index % 9) * 60 }}ms">
                <div class="flex items-start justify-between gap-2">
                    <h2 class="heading truncate text-lg leading-snug">{{ $note->title ?? 'Untitled' }}</h2>

                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-amber-400/20 to-orange-500/20 text-amber-500 opacity-0 transition-opacity duration-200 group-hover:opacity-100 dark:text-orange-300">
                        <i class="bi bi-arrow-right"></i>
                    </span>
                </div>

                <p class="line-clamp-4 whitespace-pre-line text-sm font-semibold leading-relaxed text-slate-600 dark:text-slate-400">
                    {{ $note->body }}
                </p>

                <p class="mt-auto flex items-center gap-1.5 pt-2 text-xs font-bold text-slate-400 dark:text-slate-500">
                    <i class="bi bi-clock"></i>
                    {{ $note->updated_at?->diffForHumans() }}
                </p>
            </a>
        @empty
            <div class="col-span-full py-6">
                <x-empty-state icon="bi-journal-x" title="No notes yet">
                    <x-slot:description>Capture your first thought — it will live here.</x-slot:description>

                    <x-slot:action>
                        <a href="{{ route('notes.create') }}" class="btn-primary mt-2">
                            <i class="bi bi-plus-lg"></i>Create a note
                        </a>
                    </x-slot:action>
                </x-empty-state>
            </div>
        @endforelse
    </div>
@endsection
