@extends('layouts.main')

@section('title', 'Recycle Bin')

@php($totalItems = collect($groups)->sum(fn ($group) => $group['items']->count()))

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Recycle Bin</x-slot:title>
        <x-slot:subtitle>{{ $totalItems }} {{ str('item')->plural($totalItems) }}</x-slot:subtitle>

        @if ($totalItems > 0)
            <x-slot:actions>
                <form method="POST" action="{{ route('bin.empty') }}">
                    @csrf

                    <button type="submit" data-confirm="Permanently delete ALL items in the recycle bin?" class="btn-danger">
                        <i class="bi bi-x-octagon"></i><span>Empty bin</span>
                    </button>
                </form>
            </x-slot:actions>
        @endif
    </x-page-header>

    @forelse ($groups as $type => $group)
        <section class="mb-7 animate-fade-up">
            <h2 class="heading mb-3 flex items-center gap-2 text-base">
                <i class="bi {{ $group['icon'] }} text-violet-500 dark:text-fuchsia-400"></i>
                {{ $group['label'] }}
                <span class="chip bg-slate-900/10 dark:bg-white/10">{{ $group['items']->count() }}</span>
            </h2>

            <ul class="flex flex-col gap-2.5">
                @foreach ($group['items'] as $item)
                    <li class="glass flex flex-wrap items-center gap-3 p-4 animate-fade-up"
                        style="animation-delay: {{ ($loop->index % 8) * 50 }}ms">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-900/10 text-lg text-slate-500 dark:bg-white/10 dark:text-slate-300">
                            <i class="bi {{ $group['icon'] }}"></i>
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold">
                                @switch($type)
                                    @case('notes'){{ $item->title ?? 'Untitled' }}@break
                                    @case('todos'){{ $item->title }}@break
                                    @case('events'){{ $item->title }}@break
                                    @case('cities'){{ $item->name }}@break
                                    @default{{ $item->displayTitle() }}
                                @endswitch
                            </p>

                            <p class="truncate text-xs font-bold text-slate-400 dark:text-slate-500">
                                Deleted {{ $item->deleted_at?->diffForHumans() }}

                                @if ($type === 'events' && isset($item->starts_at))
                                    · was on {{ $item->starts_at->format('M j, Y H:i') }}
                                @elseif ($type === 'notes' && filled($item->body))
                                    · {{ str($item->body)->limit(40) }}
                                @endif
                            </p>
                        </div>

                        <div class="flex shrink-0 gap-1.5">
                            <form method="POST" action="{{ route('bin.restore', [$type, $item->id]) }}">
                                @csrf

                                <button type="submit" class="btn-ghost !px-4 !py-2 text-xs" title="Restore item">
                                    <i class="bi bi-arrow-counterclockwise"></i>Restore
                                </button>
                            </form>

                            <form method="POST" action="{{ route('bin.destroy', [$type, $item->id]) }}">
                                @csrf
                                @method('DELETE')

                                <button type="submit" data-confirm="Permanently delete this item? This cannot be undone."
                                        class="icon-btn icon-btn-danger" title="Delete forever">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <x-empty-state icon="bi-trash3" title="The recycle bin is empty">
            <x-slot:description>Deleted notes, lists, events, cities and tracks wait here until restored or purged.</x-slot:description>
        </x-empty-state>
    @endforelse
@endsection
