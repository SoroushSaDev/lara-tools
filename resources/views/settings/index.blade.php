@extends('layouts.main')

@section('title', 'Settings')

@php
    // Deep links like /settings?tab=music open straight on that tool's tab.
    // tabs.js mirrors this behaviour client-side for other entry points.
    $requested = request()->query('tab');
    $activeTab = collect($tabs)->contains('key', $requested) ? $requested : 'general';
@endphp

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Settings</x-slot:title>
        <x-slot:subtitle>Pick a tool to tune its behaviour</x-slot:subtitle>
    </x-page-header>

    <form method="POST" action="{{ route('settings.update') }}" data-tabs
          class="flex flex-col gap-5 lg:grid lg:grid-cols-[240px_minmax(0,1fr)] lg:items-start lg:gap-6">
        @csrf
        @method('PUT')

        {{-- Tab rail: horizontal strip on small screens, vertical column on large --}}
        <nav class="glass flex h-fit flex-wrap items-center gap-2 p-2 animate-fade-up lg:flex-col lg:items-stretch lg:gap-1.5 lg:p-2.5">
            @foreach ($tabs as $tab)
                <button type="button" data-tab="{{ $tab['key'] }}"
                        class="tab-pill lg:w-full {{ $tab['key'] === $activeTab ? 'is-active' : '' }}">
                    <i class="bi {{ $tab['icon'] }}"></i>{{ $tab['label'] }}
                </button>
            @endforeach
        </nav>

        {{-- Panels: one tool at a time, beside the rail on large screens --}}
        <div class="min-w-0">
            @foreach ($tabs as $tab)
                <section data-tab-panel="{{ $tab['key'] }}" class="glass p-6 animate-fade-up" @if ($tab['key'] !== $activeTab) hidden @endif>
                <h2 class="heading mb-5 flex items-center gap-2 text-lg">
                    <i class="bi {{ $tab['icon'] }} text-violet-500 dark:text-fuchsia-400"></i>{{ $tab['label'] }}
                </h2>

                @if ($tab['fields'] === [])
                    <p class="text-sm font-bold text-slate-400 dark:text-slate-500">
                        No preferences for this tool yet.
                    </p>
                @endif

                @foreach ($tab['sections'] ?? [['title' => null, 'keys' => array_keys($tab['fields'])]] as $section)
                    @if (filled($section['title'] ?? null))
                        <div class="{{ $loop->first ? 'mb-3' : 'mt-8 mb-3' }} flex flex-wrap items-baseline gap-x-3 gap-y-1 border-b border-slate-900/5 pb-2 dark:border-white/10">
                            <h3 class="text-xs font-black uppercase tracking-widest text-violet-500 dark:text-fuchsia-400">{{ $section['title'] }}</h3>
                            @if (filled($section['hint'] ?? null))
                                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $section['hint'] }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Switches pack two-up, option groups keep the full width --}}
                    <div class="grid gap-3 {{ $loop->last ? '' : 'mb-6' }} sm:grid-cols-2">
                        @foreach ($section['keys'] as $key)
                            @php($field = $tab['fields'][$key])
                            <div class="{{ $field['type'] === 'switch' ? '' : 'sm:col-span-2' }}">
                                @if ($field['type'] === 'switch')
                                    <label class="flex h-full cursor-pointer items-center justify-between gap-4 rounded-xl border border-slate-900/10 bg-white/50 p-4 transition hover:bg-white dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/10">
                                        <span class="text-sm font-bold">{{ $field['label'] ?? str($field['key'])->replace('_', ' ') }}</span>

                                        <span class="relative inline-flex shrink-0">
                                            <input type="hidden" name="{{ $field['key'] }}" value="0"/>
                                            <input type="checkbox" name="{{ $field['key'] }}" value="1"
                                                   class="peer sr-only" @checked($field['current'] === '1')/>

                                            <span class="block h-7 w-12 rounded-full bg-slate-300 transition-colors peer-checked:bg-gradient-to-r peer-checked:from-violet-600 peer-checked:to-fuchsia-600 dark:bg-slate-600"></span>
                                            <span class="pointer-events-none absolute left-1 top-1 size-5 rounded-full bg-white shadow-md transition-transform peer-checked:translate-x-5"></span>
                                        </span>
                                    </label>
                                @else
                                    <p class="mb-2.5 text-sm font-bold capitalize">{{ $field['label'] ?? str($field['key'])->remove(['_format', '_start', '_precision'])->replace('_', ' ') }}</p>

                                    <div class="{{ count($field['options']) > 3 ? 'flex flex-wrap gap-2.5' : 'grid grid-cols-' . count($field['options']) . ' gap-3' }}">
                                        @foreach ($field['options'] as $value => $option)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="{{ $field['key'] }}" value="{{ $value }}"
                                                       class="peer sr-only" @checked($field['current'] == $value)/>

                                                <span class="block rounded-xl border-2 border-slate-900/10 bg-white/50 px-4 py-2.5 text-center text-sm font-bold transition-all
                                                    peer-checked:border-violet-500 peer-checked:bg-violet-500/10 peer-checked:text-violet-700 hover:bg-white
                                                    dark:border-white/10 dark:bg-white/[0.04] dark:peer-checked:text-fuchsia-300 dark:hover:bg-white/10">
                                                    {{ $option }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </section>
        @endforeach
        </div>

        {{-- Save bar --}}
        <div class="sticky bottom-24 z-20 flex justify-end lg:col-span-2 lg:bottom-8 animate-fade-up">
            <button type="submit" class="btn-primary px-8 shadow-xl">
                <i class="bi bi-check-lg"></i>Save changes
            </button>
        </div>
    </form>
@endsection
