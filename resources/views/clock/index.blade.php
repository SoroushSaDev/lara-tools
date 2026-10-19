@extends('layouts.main')

@section('title', 'Clock')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Clock</x-slot:title>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'clock']) }}" class="icon-btn" title="Clock settings">
                <i class="bi bi-gear"></i>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div id="clock-root"
         data-tabs
         data-clock-format="{{ $clockTimeFormat }}"
         data-show-seconds="{{ $clockShowSeconds ? '1' : '0' }}">

        {{-- Tab strip --}}
        <div class="glass mb-5 flex flex-wrap items-center gap-2 p-2 animate-fade-up">
            <button type="button" data-tab="world" class="tab-pill is-active"><i class="bi bi-globe-americas"></i>World Clocks</button>
            <button type="button" data-tab="stopwatch" class="tab-pill"><i class="bi bi-stopwatch"></i>Stopwatch</button>
            <button type="button" data-tab="timer" class="tab-pill"><i class="bi bi-hourglass-split"></i>Timer</button>
            <button type="button" data-tab="alarms" class="tab-pill"><i class="bi bi-alarm"></i>Alarms</button>
        </div>

        {{-- Local clock plus the pinned world clocks --}}
        <section data-tab-panel="world" class="animate-fade-up">
            <div class="mx-auto flex max-w-lg flex-col items-center gap-8 py-6">
                <div class="clock-face relative size-60 rounded-full border border-white/70 shadow-2xl shadow-violet-900/20 sm:size-72 dark:border-white/10">
                    {{-- Hour ticks --}}
                    @foreach (range(0, 11) as $tick)
                        <span class="clock-tick text-slate-500 dark:text-slate-300" style="transform: rotate({{ $tick * 30 }}deg)"></span>
                    @endforeach

                    {{-- Hands are centred with a negative margin because the
                         tick loop writes transform (rotate) directly. --}}
                    <span class="clock-hand !w-1 h-[70px] -ms-0.5 bg-slate-800 dark:bg-white" id="hand-hour"></span>
                    <span class="clock-hand !w-1 h-[95px] -ms-0.5 bg-slate-700 dark:bg-slate-200" id="hand-minute"></span>
                    <span class="clock-hand w-0.5 h-[105px] -ms-px bg-gradient-to-b from-violet-500 to-fuchsia-600" id="hand-second"></span>

                    <span class="absolute left-1/2 top-1/2 size-3 -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-br from-violet-600 to-fuchsia-600 shadow-md"></span>
                </div>

                <div class="text-center">
                    <p class="font-display text-5xl font-semibold tabular-nums tracking-tight text-slate-900 sm:text-6xl dark:text-white" id="digital-time">{{ now()->format('H:i') }}@if ($clockShowSeconds)<span id="digital-seconds" class="text-violet-500 dark:text-fuchsia-400">{{ now()->format(':s') }}</span>@endif</p>
                    <p class="mt-2 text-sm font-bold capitalize text-slate-500 dark:text-slate-400" id="digital-date">{{ now()->translatedFormat('l, F j Y') }}</p>
                </div>
            </div>

            <h2 class="heading mb-3 flex items-center gap-2 text-lg">
                <i class="bi bi-pin-angle-fill text-violet-500 dark:text-fuchsia-400"></i>Pinned cities
            </h2>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($zones as $zone)
                    <div class="glass glass-hover flex items-center gap-4 p-4 animate-fade-up">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold sm:text-lg">{{ config('clock.zones')[$zone] }}</p>
                            <p class="truncate text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $zone }}</p>
                        </div>

                        <span class="heading shrink-0 text-2xl tabular-nums" data-zone-time data-tz="{{ $zone }}">--:--</span>

                        <form method="POST" action="{{ route('clock.zones') }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="remove" value="{{ $zone }}"/>

                            <button type="submit" class="icon-btn icon-btn-danger !size-9" title="Unpin {{ config('clock.zones')[$zone] }}">
                                <i class="bi bi-pin-angle-fill"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="col-span-full">
                        <x-empty-state icon="bi-globe" title="No world clocks pinned">
                            <x-slot:description>Pin cities below to compare times at a glance.</x-slot:description>
                        </x-empty-state>
                    </div>
                @endforelse
            </div>

            @if (! empty($available))
                <form method="POST" action="{{ route('clock.zones') }}"
                      class="glass mt-4 flex flex-wrap items-center justify-center gap-3 p-4 animate-fade-up">
                    @csrf

                    <div class="dropdown relative min-w-52 flex-1">
                        <select name="add" data-dropdown required
                                class="sr-only">
                            <option value="" disabled selected>Choose a city…</option>

                            @foreach ($available as $timezone => $city)
                                <option value="{{ $timezone }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="bi bi-plus-lg"></i>Pin clock
                    </button>
                </form>
            @endif
        </section>

        {{-- Stopwatch --}}
        <section data-tab-panel="stopwatch" hidden>
            <div class="mx-auto flex max-w-lg flex-col items-center gap-6 py-6">
                <p id="sw-display" class="font-display text-6xl font-semibold tabular-nums tracking-tight sm:text-7xl">00:00.00</p>

                <div class="flex items-center gap-3">
                    <button type="button" id="sw-start" class="btn-primary px-7"><i class="bi bi-play-fill"></i>Start</button>
                    <button type="button" id="sw-lap" class="btn-ghost"><i class="bi bi-flag"></i>Lap</button>
                    <button type="button" id="sw-reset" class="btn-ghost"><i class="bi bi-arrow-counterclockwise"></i>Reset</button>
                </div>

                <ul id="sw-laps" class="flex w-full flex-col gap-2"></ul>
            </div>
        </section>

        {{-- Timer --}}
        <section data-tab-panel="timer" hidden>
            <div class="mx-auto flex max-w-lg flex-col items-center gap-6 py-6">
                <p id="timer-display" class="font-display text-6xl font-semibold tabular-nums tracking-tight sm:text-7xl">00:00</p>

                <div class="progress-track w-full max-w-sm">
                    <span id="timer-bar" class="progress-fill block" style="width: 0%"></span>
                </div>

                <div class="flex flex-wrap justify-center gap-2">
                    @foreach ([1, 3, 5, 10, 15, 25, 45, 60] as $preset)
                        <button type="button" data-timer-preset="{{ $preset }}"
                                class="chip border border-slate-900/10 bg-white/50 py-2 backdrop-blur hover:bg-white dark:border-white/10 dark:bg-white/10 dark:hover:bg-white/20">
                            {{ $preset }} min
                        </button>
                    @endforeach
                </div>

                <div class="flex items-end gap-3">
                    <label class="text-center">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Minutes</span>
                        <input type="number" id="timer-minutes" min="0" max="999" value="5"
                               class="field-input !w-24 text-center"/>
                    </label>

                    <label class="text-center">
                        <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Seconds</span>
                        <input type="number" id="timer-seconds" min="0" max="59" value="0"
                               class="field-input !w-24 text-center"/>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" id="timer-start" class="btn-primary px-7"><i class="bi bi-play-fill"></i>Start</button>
                    <button type="button" id="timer-reset" class="btn-ghost"><i class="bi bi-arrow-counterclockwise"></i>Reset</button>
                </div>

                <p class="text-xs font-bold text-slate-400 dark:text-slate-500">
                    A soft chime plays when the timer finishes.
                </p>
            </div>
        </section>

        {{-- Alarms --}}
        <section data-tab-panel="alarms" hidden>
            <div id="alarm-alert" class="glass mb-4 hidden items-center gap-3 border-rose-500/40 bg-rose-500/15 px-4 py-3" role="status">
                <i class="bi bi-alarm-fill shrink-0 text-xl text-rose-500"></i>

                <p id="alarm-alert-text" class="min-w-0 flex-1 text-sm font-bold"></p>

                <button type="button" id="alarm-dismiss" class="icon-btn icon-btn-danger !size-9 shrink-0" title="Stop alarm">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form id="alarm-form" class="glass mb-4 flex flex-wrap items-end gap-3 p-4 animate-fade-up">
                <label>
                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Time</span>
                    <input type="time" id="alarm-time" required
                           class="field-input !w-auto tabular-nums"/>
                </label>

                <label class="min-w-40 flex-1">
                    <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Label</span>
                    <input type="text" id="alarm-label" maxlength="40" placeholder="Wake up"
                           class="field-input w-full"/>
                </label>

                <label class="flex cursor-pointer items-center gap-2">
                    <span class="relative inline-flex">
                        <input type="checkbox" id="alarm-repeat" class="peer sr-only" checked/>
                        <span class="block h-7 w-12 rounded-full bg-slate-300 transition-colors peer-checked:bg-gradient-to-r peer-checked:from-violet-600 peer-checked:to-fuchsia-600 dark:bg-slate-600"></span>
                        <span class="pointer-events-none absolute left-1 top-1 size-5 rounded-full bg-white shadow-md transition-transform peer-checked:translate-x-5"></span>
                    </span>

                    <span class="text-sm font-bold">Repeat daily</span>
                </label>

                <button type="submit" class="btn-primary">
                    <i class="bi bi-plus-lg"></i>Add alarm
                </button>
            </form>

            <ul id="alarm-list" class="flex flex-col gap-3"></ul>

            <p id="alarm-empty" class="py-6 text-center text-sm font-bold text-slate-400 dark:text-slate-500">
                No alarms set yet.
            </p>

            <p class="mt-4 text-xs font-bold text-slate-400 dark:text-slate-500">
                Alarms ring while LaraTools is open.
            </p>
        </section>
    </div>
@endsection
