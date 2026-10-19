@extends('layouts.main')

@section('title', 'About')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>About</x-slot:title>
    </x-page-header>

    <div class="mx-auto max-w-xl space-y-5">
        {{-- Identity card --}}
        <section class="glass p-8 text-center animate-fade-up">
            <span class="mx-auto flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-violet-600 to-fuchsia-600 text-4xl text-white shadow-xl shadow-violet-600/40">
                <i class="bi bi-hexagon-fill"></i>
            </span>

            <h2 class="heading mt-4 text-3xl">Lara<span class="text-gradient">Tools</span></h2>
            <p class="mt-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                A cross-platform desktop companion built with Laravel, NativePHP and Tailwind CSS.
            </p>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-2.5">
                <span class="chip bg-violet-500/15 py-1.5 text-violet-700 dark:text-fuchsia-300">
                    <i class="bi bi-tag-fill"></i>Version {{ $version }}
                </span>

                <button type="button" data-changelog-open class="btn-ghost !py-1.5 text-xs">
                    <i class="bi bi-clock-history"></i>Version history
                </button>
            </div>
        </section>

        {{-- Credits --}}
        <section class="glass p-6 animate-fade-up" style="animation-delay: 60ms">
            <h3 class="heading mb-4 flex items-center gap-2 text-base">
                <i class="bi bi-stack text-sky-500"></i>Built with
            </h3>

            <ul class="grid grid-cols-2 gap-3 text-sm font-bold sm:grid-cols-2">
                @foreach ([
                    ['bi-filetype-php', 'Laravel 12'],
                    ['bi-window-desktop', 'NativePHP'],
                    ['bi-brush', 'Tailwind CSS 4'],
                    ['bi-music-note-beamed', 'getID3'],
                    ['bi-cloud-sun', 'OpenWeatherMap'],
                    ['bi-calendar-event', 'Jalali calendar API'],
                ] as [$icon, $label])
                    <li class="flex items-center gap-2.5 rounded-lg bg-slate-900/5 px-3.5 py-2.5 dark:bg-white/[0.06]">
                        <i class="bi {{ $icon }} text-violet-500 dark:text-fuchsia-400"></i>{{ $label }}
                    </li>
                @endforeach
            </ul>
        </section>
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
