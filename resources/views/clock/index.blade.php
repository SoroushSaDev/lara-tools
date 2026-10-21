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
            {{-- Two columns on lg: local clock | pin form above the pinned cities.
                 The grid lives on an inner wrapper so the panel toggle keeps
                 using the bare `hidden` attribute on the section itself. --}}
            <div class="lg:grid lg:grid-cols-[minmax(0,24rem)_minmax(0,1fr)] lg:items-start lg:gap-6">
                <div class="flex flex-col items-center gap-8 py-6">
                    {{-- Hidden rather than removed: clock.js keeps writing to the hands. --}}
                    <div class="clock-face relative size-60 rounded-full border border-white/70 shadow-2xl shadow-violet-900/20 sm:size-72 dark:border-white/10"
                         @if (! $clockShowAnalog) hidden @endif>
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

                <div class="min-w-0">
                    {{-- The picker sits above the list: every timezone is on offer,
                         so choosing comes first and the pins are what it feeds. --}}
                    <form method="POST" action="{{ route('clock.zones') }}"
                          class="glass flex flex-wrap items-center justify-center gap-3 p-4 animate-fade-up">
                        @csrf

                        <div class="dropdown relative min-w-52 flex-1">
                            <select name="add" data-dropdown data-dropdown-filter required
                                    class="sr-only">
                                <option value="" disabled selected>Choose a city…</option>

                                {{-- One group per region, curated cities inside it. --}}
                                @foreach ($available as $region => $cities)
                                    <optgroup label="{{ $region }}">
                                        @foreach ($cities as $timezone => $city)
                                            <option value="{{ $timezone }}">{{ $city }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="bi bi-plus-lg"></i>Pin clock
                        </button>
                    </form>

                    <h2 class="heading mt-5 mb-3 flex items-center gap-2 text-lg">
                        <i class="bi bi-pin-angle-fill text-violet-500 dark:text-fuchsia-400"></i>Pinned cities
                    </h2>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-2">
                        @forelse ($zoneLabels as $zone => $city)
                            <div class="glass glass-hover flex items-center gap-4 p-4 animate-fade-up">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold sm:text-lg">{{ $city }}</p>
                                    <p class="truncate text-[11px] font-bold text-slate-400 dark:text-slate-500">{{ $zone }}</p>
                                </div>

                                <span class="heading shrink-0 text-2xl tabular-nums" data-zone-time data-tz="{{ $zone }}">--:--</span>

                                <form method="POST" action="{{ route('clock.zones') }}" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="remove" value="{{ $zone }}"/>

                                    <button type="submit" class="icon-btn icon-btn-danger !size-9" title="Unpin {{ $city }}">
                                        <i class="bi bi-pin-angle-fill"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-full">
                                <x-empty-state icon="bi-globe" title="No world clocks pinned">
                                    <x-slot:description>Pin cities above to compare times at a glance.</x-slot:description>
                                </x-empty-state>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
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
            {{-- Two columns on lg: alert + form | alarm list. The grid is on
                 an inner wrapper; the panel keeps the bare `hidden` attribute
                 on the section for tabs.js. --}}
            <div class="lg:grid lg:grid-cols-[minmax(0,34rem)_minmax(0,1fr)] lg:items-start lg:gap-6">
                <div class="min-w-0">
                    <div id="alarm-alert" class="glass mb-4 hidden items-center gap-3 border-rose-500/40 bg-rose-500/15 px-4 py-3" role="status">
                        <i class="bi bi-alarm-fill shrink-0 text-xl text-rose-500"></i>

                        <p id="alarm-alert-text" class="min-w-0 flex-1 text-sm font-bold"></p>

                        <button type="button" id="alarm-dismiss" class="icon-btn icon-btn-danger !size-9 shrink-0" title="Stop alarm">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <form id="alarm-form" class="glass mb-4 flex flex-col gap-4 p-5 animate-fade-up">
                        <div class="flex flex-wrap items-end gap-3">
                            <div>
                                <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Time</span>

                                {{-- Reads as HH:MM from #alarm-time, the component's hidden value. --}}
                                <x-time-picker id="alarm-time" format="{{ $clockTimeFormat }}"/>

                                {{-- Hidden inputs take part in no constraint validation, so the
                                     missing-time message lives here. --}}
                                <p id="alarm-time-error" class="field-error" hidden>Pick an hour and a minute.</p>
                            </div>

                            <label class="min-w-40 flex-1">
                                <span class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-400">Label</span>
                                <input type="text" id="alarm-label" maxlength="40" placeholder="Wake up"
                                       class="field-input w-full"/>
                            </label>
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Repeats</span>

                                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500">
                                    Pick nothing for a one-time alarm
                                </span>
                            </div>

                            {{-- 'Every day' is the master toggle: it fills the seven chips
                                 and empties them again on the next click. --}}
                            <div id="alarm-days" class="mt-2 flex flex-wrap items-center gap-1.5">
                                <button type="button" class="day-chip day-chip-wide" data-alarm-every
                                        aria-pressed="false">Every day</button>

                                <span class="me-1 h-7 w-px bg-slate-900/10 dark:bg-white/10" aria-hidden="true"></span>

                                @foreach (['Sun' => 'Sunday', 'Mon' => 'Monday', 'Tue' => 'Tuesday', 'Wed' => 'Wednesday', 'Thu' => 'Thursday', 'Fri' => 'Friday', 'Sat' => 'Saturday'] as $short => $full)
                                    <button type="button" class="day-chip" data-alarm-day="{{ $loop->index }}"
                                            aria-pressed="false" title="{{ $full }}">{{ $short }}</button>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="btn-primary self-end">
                            <i class="bi bi-plus-lg"></i>Add alarm
                        </button>
                    </form>

                    <p class="text-xs font-bold text-slate-400 dark:text-slate-500">
                        Alarms ring while LaraTools is open.
                    </p>
                </div>

                <div class="min-w-0 lg:mt-0">
                    <ul id="alarm-list" class="flex flex-col gap-3"></ul>

                    <p id="alarm-empty" class="py-6 text-center text-sm font-bold text-slate-400 dark:text-slate-500">
                        No alarms set yet.
                    </p>
                </div>
            </div>
        </section>
    </div>
@endsection
