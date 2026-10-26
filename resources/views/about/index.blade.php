@extends('layouts.main')

@section('title', 'About')

@section('content')
@php
    $card = 'glass scroll-mt-24 p-6 animate-fade-up sm:p-7';
    $sectionTitle = 'heading mb-4 flex items-center gap-2.5 text-lg';
    $sectionIcon = 'shrink-0 text-violet-500 dark:text-fuchsia-400';
    $bullet = 'flex items-start gap-2.5 text-sm font-semibold leading-relaxed text-slate-600 dark:text-slate-300';
    $bulletIcon = 'mt-1 shrink-0 text-[10px] text-violet-500 dark:text-fuchsia-400';
    $key = 'rounded-md border border-slate-900/10 bg-white/70 px-1.5 py-0.5 text-xs font-bold text-slate-700 dark:border-white/15 dark:bg-white/10 dark:text-slate-200';
    $label = 'text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500';
    $latest = $changelog[0] ?? null;
@endphp

<x-page-header back="{{ route('home') }}" back-label="Home">
    <x-slot:title>About</x-slot:title>
    <x-slot:subtitle>Your tools, your machine — version {{ $version }}</x-slot:subtitle>
</x-page-header>

<div class="lg:grid lg:grid-cols-[minmax(0,20rem)_minmax(0,1fr)] lg:items-start lg:gap-6">
    {{-- Left rail: identity, quick facts and in-page navigation --}}
    <aside class="space-y-5 lg:sticky lg:top-6">
        <section class="glass p-7 text-center animate-fade-up">
            <span class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-4xl text-white shadow-xl shadow-violet-600/40">
                <i class="bi bi-hexagon-fill"></i>
            </span>

            <h2 class="heading mt-4 text-3xl">Lara<span class="text-gradient">Tools</span></h2>
            <p class="mt-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                A single-user desktop toolbox: notes, lists, calendar, music and more, built with Laravel, NativePHP and Tailwind CSS.
            </p>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
                <span class="chip bg-violet-500/15 py-1.5 text-violet-700 dark:text-fuchsia-300">
                    <i class="bi bi-tag-fill"></i>Version {{ $version }}
                </span>

                <a href="#version-history" data-turbo="false" class="btn-ghost !py-1.5 text-xs">
                    <i class="bi bi-clock-history"></i>Version history
                </a>
            </div>
        </section>

        <section class="glass p-5 animate-fade-up" style="animation-delay: 60ms">
            <h3 class="{{ $label }} mb-3">Quick facts</h3>

            <dl class="space-y-3">
                @foreach ($facts as $fact)
                    <div>
                        <dt class="{{ $label }}">
                            <i class="bi {{ $fact['icon'] }} {{ $sectionIcon }} me-1.5"></i>{{ $fact['label'] }}
                        </dt>
                        <dd class="mt-0.5 text-sm font-bold">{{ $fact['value'] }}</dd>

                        @if (filled($fact['note'] ?? null))
                            <p class="mt-0.5 text-xs font-semibold text-slate-400 dark:text-slate-500">{{ $fact['note'] }}</p>
                        @endif
                    </div>
                @endforeach
            </dl>
        </section>

        <nav class="glass p-5 animate-fade-up" style="animation-delay: 120ms" aria-label="Sections">
            <h3 class="{{ $label }} mb-3">On this page</h3>

            <ul class="space-y-0.5">
                @foreach ($sections as $section)
                    <li>
                        <a href="#{{ $section['id'] }}" data-turbo="false"
                           class="-mx-1.5 flex items-center gap-2.5 rounded-lg px-1.5 py-1.5 text-sm font-bold text-slate-600 transition-colors hover:bg-violet-500/10 hover:text-violet-700 dark:text-slate-300 dark:hover:text-fuchsia-200">
                            <i class="bi {{ $section['icon'] }} {{ $sectionIcon }}"></i>{{ $section['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>
    </aside>

    {{-- Right column: the scrollable content --}}
    <div class="mt-5 space-y-5 lg:mt-0">
        <section id="overview" class="{{ $card }}">
            <h2 class="{{ $sectionTitle }}">
                <i class="bi bi-info-circle {{ $sectionIcon }}"></i>What LaraTools is
            </h2>

            <ul class="space-y-2.5">
                @foreach ([
                    'One window, many tools: notes, todo lists, calendar, music, calculator, clock, converter, weather, plus a recycle bin and settings.',
                    'Single user by design — there is no authentication layer, no accounts and no server holding your data.',
                    'Everything you create lives in one SQLite file on this machine; copying that file is the whole backup strategy.',
                    'No telemetry, no analytics, no crash reporting. Nothing you write is uploaded.',
                    'Pages swap instantly through Turbo and music keeps playing while you navigate.',
                    'Light, dark or system theme, applied before the first paint so there is no flash.',
                ] as $point)
                    <li class="{{ $bullet }}">
                        <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>{{ $point }}
                    </li>
                @endforeach
            </ul>
        </section>

        <section id="tools" class="scroll-mt-24">
            <div class="mb-3 space-y-1">
                <h2 class="heading flex items-center gap-2.5 text-lg">
                    <i class="bi bi-tools {{ $sectionIcon }}"></i>The tools
                </h2>
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    One card per tool, with what it actually does. Each title opens the tool.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 2xl:grid-cols-2">
                @foreach ($tools as $index => $tool)
                    <article id="tool-{{ $tool['key'] }}"
                             class="glass scroll-mt-24 flex flex-col p-5 animate-fade-up"
                             style="animation-delay: {{ ($index % 6) * 50 }}ms">
                        <h3 class="heading flex items-center gap-2.5 text-base">
                            <i class="bi {{ $tool['icon'] }} text-violet-500 dark:text-fuchsia-400"></i>
                            <a href="{{ route($tool['route']) }}" class="hover:text-violet-600 dark:hover:text-fuchsia-300">
                                {{ $tool['label'] }}
                            </a>
                            <i class="bi bi-box-arrow-up-right ms-auto text-xs text-slate-400 dark:text-slate-500"></i>
                        </h3>

                        <p class="mt-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $tool['summary'] }}</p>

                        <ul class="mt-3 space-y-2">
                            @foreach ($tool['points'] as $point)
                                <li class="{{ $bullet }}">
                                    <i class="bi bi-dash-circle-fill {{ $bulletIcon }}"></i>{{ $point }}
                                </li>
                            @endforeach
                        </ul>
                    </article>
                @endforeach
            </div>
        </section>

        <section id="keyboard" class="{{ $card }}">
            <h2 class="{{ $sectionTitle }}">
                <i class="bi bi-keyboard {{ $sectionIcon }}"></i>Keyboard &amp; input
            </h2>

            <p class="{{ $label }} mb-2">Calculator keypad (Standard and Scientific)</p>

            <ul class="space-y-2">
                <li class="{{ $bullet }}">
                    <span class="flex flex-wrap gap-1">
                        <kbd class="{{ $key }}">0-9</kbd><kbd class="{{ $key }}">.</kbd><kbd class="{{ $key }}">+ - * / %</kbd><kbd class="{{ $key }}">^ ! ( )</kbd>
                    </span>
                    append to the expression.
                </li>
                <li class="{{ $bullet }}">
                    <span class="flex flex-wrap gap-1">
                        <kbd class="{{ $key }}">Enter</kbd><kbd class="{{ $key }}">=</kbd>
                    </span>
                    evaluate, <kbd class="{{ $key }}">Backspace</kbd> delete, <kbd class="{{ $key }}">Escape</kbd> clear.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>Keys are ignored while a BMI or Date field has focus, so you can type in them normally.
                </li>
            </ul>

            <p class="{{ $label }} mb-2 mt-5">Editing</p>

            <ul class="space-y-2">
                <li class="{{ $bullet }}">
                    <span class="flex flex-wrap gap-1">
                        <kbd class="{{ $key }}">Ctrl/⌘ B</kbd><kbd class="{{ $key }}">I</kbd><kbd class="{{ $key }}">U</kbd><kbd class="{{ $key }}">K</kbd>
                    </span>
                    bold, italic, underline and the link field in the note editor.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>In a note: <kbd class="{{ $key }}">Enter</kbd> starts a paragraph, <kbd class="{{ $key }}">Shift Enter</kbd> a line break, <kbd class="{{ $key }}">Esc</kbd> leaves the link field.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>In a todo list: <kbd class="{{ $key }}">Enter</kbd> adds the next item; <kbd class="{{ $key }}">Backspace</kbd> on an empty row removes it and focuses the row above.
                </li>
            </ul>

            <p class="{{ $label }} mb-2 mt-5">Lists, dialogs and sliders</p>

            <ul class="space-y-2">
                <li class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>Custom dropdowns: <kbd class="{{ $key }}">↑ ↓</kbd> move, <kbd class="{{ $key }}">Home End</kbd> jump, <kbd class="{{ $key }}">Enter</kbd> selects, <kbd class="{{ $key }}">Esc</kbd> closes and returns focus.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i><kbd class="{{ $key }}">Esc</kbd> also closes the version-history modal, the confirmation dialog and an open dropdown.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>Sliders (player volume, seek bar) stay native range inputs: focus one and drive it with the arrow keys.
                </li>
            </ul>
        </section>

        <section id="data" class="{{ $card }}">
            <h2 class="{{ $sectionTitle }}">
                <i class="bi bi-hdd {{ $sectionIcon }}"></i>Data &amp; privacy
            </h2>

            <p class="{{ $label }} mb-2">Where everything lives</p>

            <dl class="space-y-2.5 rounded-xl bg-slate-900/5 p-4 dark:bg-white/[0.06]">
                <div>
                    <dt class="{{ $label }}">Records</dt>
                    <dd class="mt-0.5 text-sm font-semibold">
                        Notes, todo lists, events, saved cities, track metadata and preferences in one SQLite file in the app's own data folder.
                    </dd>
                </div>

                <div>
                    <dt class="{{ $label }}">Imported audio</dt>
                    <dd class="mt-0.5 text-sm font-semibold">
                        Copies — never your originals — in the app's own music folder.
                    </dd>
                </div>

                <div>
                    <dt class="{{ $label }}">Album art</dt>
                    <dd class="mt-0.5 text-sm font-semibold">
                        Extracted from ID3 tags on import.
                    </dd>
                </div>

                <div>
                    <dt class="{{ $label }}">Cached and session rows</dt>
                    <dd class="mt-0.5 text-sm font-semibold">Holiday responses and session records sit in the <span class="{{ $key }}">cache</span> and <span class="{{ $key }}">sessions</span> tables of the same file.</dd>
                </div>

                <div>
                    <dt class="{{ $label }}">Client-side state</dt>
                    <dd class="mt-0.5 text-sm font-semibold">
                        Theme choice, player volume, calculator history and mode switches, clock alarms — localStorage keys prefixed <span class="{{ $key }}">lara-tools.</span>
                    </dd>
                </div>
            </dl>

            <p class="{{ $label }} mb-2 mt-5">What leaves the machine</p>

            <ul class="space-y-2.5">
                <li class="{{ $bullet }}">
                    <i class="bi bi-arrow-up-circle-fill {{ $bulletIcon }}"></i>
                    Weather: one HTTPS request to <span class="{{ $key }}">api.openweathermap.org</span> carrying the city name or coordinates you look up and your API key.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-arrow-up-circle-fill {{ $bulletIcon }}"></i>
                    Holidays: HTTPS requests to <span class="{{ $key }}">pnldev.com/api/calender</span> for the Jalali dates in the visible month, cached for six hours.
                </li>
                <li class="{{ $bullet }}">
                    <i class="bi bi-arrow-up-circle-fill {{ $bulletIcon }}"></i>
                    Map tiles: the webview loads <span class="{{ $key }}">tile.openstreetmap.org</span> images when an event form or event shows a map.
                </li>
            </ul>

            <p class="{{ $bullet }} mt-4">
                <i class="bi bi-shield-check {{ $bulletIcon }}"></i>
                Everything else stays on disk: note bodies, checklist items, event titles and locations, file names, tags, saved preferences. The one secret the app holds is your weather API key, which you type into Settings, lives in this machine's database, and travels only to OpenWeatherMap with a forecast request.
            </p>
        </section>

        <section id="credits" class="{{ $card }}">
            <h2 class="{{ $sectionTitle }}">
                <i class="bi bi-box-seam {{ $sectionIcon }}"></i>Third-party &amp; credits
            </h2>

            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach ($credits as $credit)
                    <li class="flex items-start gap-2.5 rounded-xl bg-slate-900/5 p-3.5 dark:bg-white/[0.06]">
                        <i class="bi {{ $credit['icon'] }} mt-0.5 shrink-0 text-violet-500 dark:text-fuchsia-400"></i>

                        <div class="min-w-0">
                            <p class="text-sm font-bold">{{ $credit['name'] }}</p>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $credit['role'] }}</p>
                            <span class="chip mt-1.5 bg-violet-500/15 text-violet-700 dark:text-fuchsia-300">{{ $credit['version'] }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>

            <p class="mt-4 text-xs font-semibold text-slate-500 dark:text-slate-400">
                <i class="bi bi-patch-check me-1 text-violet-500 dark:text-fuchsia-400"></i>
                The composer manifest declares the {{ $licence }} licence. Version strings above come from
                <span class="{{ $key }}">composer.json</span> <span class="{{ $key }}">package.json</span> and the running framework.
            </p>
        </section>

        <section id="version-history" class="{{ $card }}">
            <h2 class="{{ $sectionTitle }}">
                <i class="bi bi-clock-history {{ $sectionIcon }}"></i>Version history
            </h2>

            @if ($latest)
                <p class="{{ $bullet }}">
                    <i class="bi bi-circle-fill {{ $bulletIcon }}"></i>
                    Running {{ $version }} — released {{ \Carbon\Carbon::parse($latest['date'])->format('M j, Y') }}.
                    {{ count($changelog) }} releases are recorded in the timeline.
                </p>
            @endif

            <button type="button" data-changelog-open class="btn-primary mt-4">
                <i class="bi bi-list-ul"></i>Show the full timeline
            </button>
        </section>

        <section id="faq" class="{{ $card }}">
            <h2 class="{{ $sectionTitle }}">
                <i class="bi bi-wrench {{ $sectionIcon }}"></i>Troubleshooting &amp; FAQ
            </h2>

            <dl class="divide-y divide-slate-900/10 dark:divide-white/10">
                @foreach ([
                    ['A track will not play', 'Its copy in the app\'s music folder is missing or was moved. Import the file, or the folder that holds it, again — the importer replaces the broken record.'],
                    ['Weather is empty or shows an error', 'Weather needs an OpenWeatherMap key in the app\'s environment settings and a connection. Refresh the city, then check the units in Settings › Weather.'],
                    ['No holidays in the month view', 'Turn holidays back on in Settings › Calendar. The feed needs the network the first time a month is fetched; six hours of cache covers reloads.'],
                    ['An alarm did not ring', 'Alarms only fire while the Clock tool is open and the app is running. They live in this profile\'s localStorage, so another profile starts empty.'],
                    ['The event map is blank', 'Tiles come from OpenStreetMap and need the network. The picker still stores your coordinates, and the saved location opens on the map later.'],
                    ['A file dialog opens unexpectedly', 'Older builds let Turbo prefetch the music import request, which opened the native dialog on its own. Restart with composer native:dev — the import forms now opt out of prefetching.'],
                    ['The theme ignores Settings', 'The choice saved in localStorage wins over the server-side preference so a sidebar toggle is never reverted. Pick it again in the sidebar or Settings › General.'],
                    ['Something was deleted by mistake', 'Notes, todo lists, events, cities and tracks are soft-deleted. The Recycle Bin restores them until you purge it.'],
                ] as [$question, $answer])
                    <div class="py-3.5 first:pt-0 last:pb-0">
                        <dt class="text-sm font-bold">{{ $question }}</dt>
                        <dd class="mt-1 text-sm font-semibold leading-relaxed text-slate-600 dark:text-slate-300">{{ $answer }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>
</div>

{{-- Version history modal --}}
<div id="changelog-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" data-changelog-close></div>

    <div class="glass relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden animate-pop-in">
        <header class="flex items-center justify-between border-b border-slate-900/10 p-5 dark:border-white/10">
            <h2 class="heading flex items-center gap-2 text-lg">
                <i class="bi bi-clock-history text-fuchsia-500"></i>Version history
            </h2>

            <button type="button" data-changelog-close class="icon-btn !size-9 !border-0 !bg-transparent" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="overflow-y-auto p-6 pt-7">
            @foreach ($changelog as $index => $release)
                <article class="timeline-item {{ $loop->last ? '!pb-0' : '' }}">
                    <header class="mb-3 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="heading text-lg">v{{ $release['version'] }}</span>

                        @if ($index === 0)
                            <span class="chip bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white">Latest</span>
                        @endif

                        <time class="text-xs font-bold text-slate-400 dark:text-slate-500">
                            <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($release['date'])->format('M j, Y') }}
                        </time>
                    </header>

                    <ul class="space-y-2">
                        @foreach ($release['notes'] as $note)
                            <li class="flex items-start gap-2.5 text-sm font-semibold leading-relaxed">
                                <i class="bi bi-plus-circle-fill mt-0.5 shrink-0 text-xs text-emerald-500"></i>
                                {{ $note }}
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </div>
</div>
@endsection
