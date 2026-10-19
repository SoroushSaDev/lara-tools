@extends('layouts.main')

@section('title', 'Manage Cities')

@php($homeTown = app(\App\Contracts\SettingRepository::class)->get('home-town'))

@section('content')
    <x-page-header back="{{ route('weather.index') }}" back-label="Weather">
        <x-slot:title>Manage Cities</x-slot:title>
        <x-slot:subtitle>{{ $cities->count() }} saved</x-slot:subtitle>
    </x-page-header>

    {{-- Search --}}
    <form method="GET" action="{{ route('weather.cities.index') }}"
          class="glass mb-5 flex items-center gap-2 p-2 animate-fade-up">
        <i class="bi bi-search ms-3 shrink-0 text-slate-400"></i>

        <input type="search" name="search" value="{{ $search }}" autocomplete="off"
               class="w-full bg-transparent py-2 text-sm font-semibold outline-none placeholder:text-slate-400"
               placeholder="Search your cities, or any city worldwide..."/>

        @if (filled($search))
            <a href="{{ route('weather.cities.index') }}" class="icon-btn !size-9 !border-0 !bg-transparent" title="Clear search">
                <i class="bi bi-x-lg"></i>
            </a>
        @endif

        <button type="submit" class="btn-primary shrink-0">Search</button>
    </form>

    @if (isset($error))
        <div class="glass mb-4 flex items-center gap-3 border-l-4 !border-l-rose-500 p-4">
            <i class="bi bi-exclamation-circle-fill shrink-0 text-xl text-rose-500"></i>
            <p class="text-sm font-bold">{{ $error }}</p>
        </div>
    @endif

    <ul class="grid grid-cols-1 gap-3 xl:grid-cols-2">
        @forelse ($cities as $city)
            <li class="glass glass-hover flex items-center gap-4 p-4 animate-fade-up"
                style="animation-delay: {{ ($loop->index % 10) * 50 }}ms">
                <a href="{{ route('weather.index', ['city' => $city->name]) }}" class="flex min-w-0 flex-1 items-center gap-4">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-sky-400/20 to-blue-600/20 text-2xl text-sky-600 dark:text-sky-300">
                        <i class="bi {{ weatherIcon($city->main) }}"></i>
                    </span>

                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-1.5 truncate font-bold sm:text-lg">
                            {{ $city->name }}
                            @if ($city->name === $homeTown)
                                <i class="bi bi-star-fill shrink-0 text-sm text-amber-400" title="Home town"></i>
                            @endif
                        </p>
                        <p class="truncate text-xs font-bold capitalize text-slate-500 dark:text-slate-400">
                            {{ $city->main ?? '—' }}
                        </p>
                    </div>

                    <span class="heading shrink-0 text-3xl sm:text-4xl">
                        {{ round($city->temperature) }}{{ temperatureUnitSymbol($weatherUnits) }}
                    </span>
                </a>

                <div class="flex shrink-0 gap-1.5">
                    <form method="POST" action="{{ route('weather.set', $city) }}">
                        @csrf

                        <button type="submit" class="icon-btn hover:!text-amber-400" title="{{ $city->isHomeTown() ? 'Unset home town' : 'Set as home town' }}">
                            <i class="bi bi-star{{ $city->isHomeTown() ? '-fill text-amber-400' : '' }}"></i>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('weather.refresh', $city) }}">
                        @csrf

                        <button type="submit" class="icon-btn hover:!text-sky-500" title="Refresh snapshot">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('weather.destroy', $city) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" data-confirm="Move {{ $city->name }} to the recycle bin?" class="icon-btn icon-btn-danger" title="Remove city">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>
            </li>
        @empty
            @if ($remote && isset($remote['main']))
                <li class="col-span-full">
                    <div class="glass flex items-center gap-4 p-4 animate-pop-in">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-sky-400/20 to-blue-600/20 text-2xl text-sky-600 dark:text-sky-300">
                            <i class="bi {{ weatherIcon($remote['weather'][0]['main'] ?? null) }}"></i>
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate font-bold sm:text-lg">{{ $remote['name'] }}</p>
                            <p class="truncate text-xs font-bold capitalize text-slate-500 dark:text-slate-400">
                                {{ $remote['weather'][0]['description'] ?? '' }}
                            </p>
                        </div>

                        <span class="heading shrink-0 text-3xl sm:text-4xl">
                            {{ round($remote['main']['temp']) }}{{ temperatureUnitSymbol($weatherUnits) }}
                        </span>

                        <form method="POST" action="{{ route('weather.cities.store') }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="weather" value="{{ json_encode($remote) }}"/>

                            <button type="submit" class="btn-primary">
                                <i class="bi bi-plus-lg"></i><span>Add</span>
                            </button>
                        </form>
                    </div>
                </li>
            @elseif (! filled($search))
                <li class="col-span-full py-6">
                    <x-empty-state icon="bi-geo-alt" title="No cities saved yet">
                        <x-slot:description>Search above to find and add your first city.</x-slot:description>
                    </x-empty-state>
                </li>
            @else
                <li class="col-span-full py-6 text-center text-sm font-bold text-slate-400 dark:text-slate-500">
                    No saved city matches "{{ $search }}".
                </li>
            @endif
        @endforelse
    </ul>
@endsection
