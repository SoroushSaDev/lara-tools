@extends('layouts.main')

@section('title', 'Calendar')

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Calendar</x-slot:title>
        <x-slot:subtitle>{{ $date->translatedFormat('l, F j Y') }}</x-slot:subtitle>

        <x-slot:actions>
            <a href="{{ route('settings', ['tab' => 'calendar']) }}" class="icon-btn" title="Calendar settings">
                <i class="bi bi-gear"></i>
            </a>

            <a href="{{ route('calendar.create', ['date' => $date->toDateString()]) }}" class="btn-primary">
                <i class="bi bi-plus-lg"></i><span class="hidden sm:inline">Add event</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    <div id="calendar-root"
         data-base-url="{{ route('calendar.index') }}"
         data-date="{{ $date->toDateString() }}"
         data-event-days='@json($eventDays)'
         data-week-start="{{ $weekStart }}">
        {{-- Month navigation --}}
        <div class="glass mb-5 flex items-center justify-between gap-2 p-3 animate-fade-up">
            <button type="button" class="icon-btn" data-calendar-shift="-1" title="Previous month">
                <i class="bi bi-chevron-left"></i>
            </button>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <select id="calendar-month"
                        class="field-input !w-auto cursor-pointer appearance-none !py-2 text-center font-bold">
                    @foreach (range(1, 12) as $month)
                        <option value="{{ $month }}" @selected($month === $date->month)>
                            {{ Carbon\Carbon::create()->month($month)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <select id="calendar-year"
                        class="field-input !w-auto cursor-pointer appearance-none !py-2 text-center font-bold">
                    @foreach (range(now()->year - 6, now()->year + 6) as $year)
                        <option value="{{ $year }}" @selected($year === $date->year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="icon-btn" data-calendar-shift="1" title="Next month">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        {{-- Day grid (headers + cells rendered by JS) --}}
        <div class="glass p-3 animate-fade-up sm:p-5" style="animation-delay: 60ms">
            <div id="calendar-grid" class="grid grid-cols-7 gap-1.5 sm:gap-2"></div>
        </div>

        {{-- Selected day details --}}
        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <section class="glass p-5 animate-fade-up" style="animation-delay: 120ms">
                <h3 class="heading mb-3 flex items-center gap-2 text-base">
                    <i class="bi bi-stars text-fuchsia-500"></i>Your events · {{ $date->format('M j') }}
                </h3>

                @forelse ($events as $event)
                    <div class="group flex items-center gap-3 rounded-xl border-l-4 border-violet-500 bg-violet-500/10 px-4 py-3 transition-colors hover:bg-violet-500/20 dark:border-fuchsia-500 dark:bg-white/[0.04]">
                        <span class="shrink-0 rounded-lg bg-gradient-to-br from-violet-600 to-fuchsia-600 px-2 py-1 text-xs font-bold text-white shadow-sm">
                            {{ $event->starts_at->format('H:i') }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold">{{ $event->title }}</p>

                            @if ($event->description)
                                <p class="truncate text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $event->description }}</p>
                            @endif

                            @if ($event->address)
                                <p class="truncate text-xs font-semibold text-slate-400 dark:text-slate-500">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $event->address }}
                                </p>
                            @endif
                        </div>

                        <div class="flex shrink-0 gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                            <a href="{{ route('calendar.edit', $event) }}" class="icon-btn size-8 !border-0 !bg-transparent" title="Edit event">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form method="POST" action="{{ route('calendar.destroy', $event) }}">
                                @csrf
                                @method('DELETE')

                                <button type="submit" data-confirm="Delete this event?" class="icon-btn size-8 !border-0 !bg-transparent hover:!text-rose-500" title="Delete event">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-bold text-slate-400 dark:text-slate-500">
                        Nothing planned — enjoy the calm.
                    </p>
                @endforelse
            </section>

            <section class="glass p-5 animate-fade-up" style="animation-delay: 180ms" @if (! $holidaysEnabled) hidden @endif>
                <h3 class="heading mb-3 flex items-center gap-2 text-base">
                    <i class="bi bi-globe-americas text-sky-500"></i>Holidays &amp; observances
                </h3>

                @forelse ($holidays as $holiday)
                    <div class="flex items-start gap-2.5 rounded-xl bg-sky-500/10 px-4 py-3">
                        <i class="bi bi-flag-fill mt-0.5 shrink-0 text-sky-500"></i>
                        <p class="text-sm leading-relaxed">{{ $holiday }}</p>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm font-bold text-slate-400 dark:text-slate-500">
                        No holidays on this day.
                    </p>
                @endforelse
            </section>
        </div>
    </div>
@endsection
