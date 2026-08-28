@extends('layouts.main')

@section('title', 'Weather')

@php
    $unitSymbol = temperatureUnitSymbol($weatherUnits);
@endphp

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Weather</x-slot:title>

        @if ($city)
            <x-slot:subtitle>
                {{ $city->isHomeTown() ? 'Your home town · ' : '' }}{{ $city->country_code }}
            </x-slot:subtitle>
        @elseif ($isLiveLocation)
            <x-slot:subtitle>Live location</x-slot:subtitle>
        @endif

        <x-slot:actions>
            @if ($city && ! $city->isHomeTown())
                <form method="POST" action="{{ route('weather.set', $city) }}">
                    @csrf

                    <button type="submit" class="icon-btn hover:!text-amber-400" title="Set as home town">
                        <i class="bi bi-star"></i>
                    </button>
                </form>
            @endif

            <a href="{{ route('settings', ['tab' => 'weather']) }}" class="icon-btn" title="Weather settings">
                <i class="bi bi-gear"></i>
            </a>

            <button type="button"
                    data-locate-url="{{ route('weather.locate') }}"
                    class="icon-btn hover:!text-sky-500"
                    title="Use my location">
                <i class="bi bi-geo-alt-fill"></i>
            </button>

            @if ($city)
                <form method="POST" action="{{ route('weather.refresh', $city) }}">
                    @csrf

                    <button type="submit" class="icon-btn hover:!text-sky-500" title="Refresh weather">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </form>
            @endif

            <a href="{{ route('weather.cities.index') }}" class="btn-ghost" title="Manage cities">
                <i class="bi bi-pencil"></i><span class="hidden sm:inline">Cities</span>
            </a>
        </x-slot:actions>
    </x-page-header>

    @if (isset($error))
        <div class="glass mb-4 flex items-center gap-3 border-l-4 !border-l-rose-500 p-4 animate-fade-up">
            <i class="bi bi-wifi-off shrink-0 text-xl text-rose-500"></i>
            <p class="text-sm font-bold">{{ $error }}</p>
        </div>
    @endif

    @if ($weather && isset($weather['main']))
        <div class="mx-auto flex max-w-lg flex-col items-center gap-6 text-center">
            {{-- Condition hero --}}
            <div class="flex flex-col items-center gap-2 animate-fade-up">
                <div class="relative">
                    <i class="bi {{ weatherIcon($weather['weather'][0]['main'] ?? null) }} bg-gradient-to-br from-sky-400 to-blue-600 bg-clip-text text-[7rem] leading-none text-transparent drop-shadow-lg sm:text-[9rem]"></i>
                    <span class="absolute inset-0 -z-10 mx-auto mt-6 size-24 rounded-full bg-sky-400/25 blur-2xl"></span>
                </div>

                <h2 class="heading text-6xl sm:text-7xl">
                    {{ round($weather['main']['temp']) }}{{ $unitSymbol }}
                </h2>

                <p class="text-xl font-bold capitalize text-slate-600 dark:text-slate-300">
                    {{ $weather['weather'][0]['description'] ?? '—' }}
                </p>

                <p class="flex items-center gap-2 text-lg font-bold">
                    @if ($isLiveLocation || ($city?->name) !== ($weather['name'] ?? null))
                        <i class="bi bi-geo-alt-fill text-sky-500"></i>
                    @endif
                    {{ $weather['name'] }}
                    @if (! $isLiveLocation && $city && $city->isHomeTown())
                        <i class="bi bi-star-fill text-sm text-amber-400" title="Home town"></i>
                    @endif
                </p>
            </div>

            {{-- Detail grid --}}
            <dl class="glass grid w-full grid-cols-2 gap-x-6 gap-y-5 p-6 text-left animate-fade-up sm:grid-cols-3" style="animation-delay: 80ms">
                @include('weather.partial.property', ['label' => 'Feels like', 'value' => round($weather['main']['feels_like']).$unitSymbol, 'icon' => 'bi-thermometer-half'])
                @include('weather.partial.property', ['label' => 'Humidity', 'value' => $weather['main']['humidity'].'%', 'icon' => 'bi-droplet-half'])
                @include('weather.partial.property', ['label' => 'Pressure', 'value' => $weather['main']['pressure'].' hPa', 'icon' => 'bi-speedometer'])
                @include('weather.partial.property', ['label' => 'Wind speed', 'value' => $weather['wind']['speed'].' m/s', 'icon' => 'bi-wind'])
                @include('weather.partial.property', ['label' => 'Wind dir.', 'value' => $weather['wind']['deg'].'°', 'icon' => 'bi-compass'])

                @if (isset($weather['sys']['sunrise']))
                    @include('weather.partial.property', ['label' => 'Sunrise', 'value' => date('H:i', (int) $weather['sys']['sunrise']), 'icon' => 'bi-sunrise'])
                @endif
                @isset($weather['sys']['sunset'])
                    @include('weather.partial.property', ['label' => 'Sunset', 'value' => date('H:i', (int) $weather['sys']['sunset']), 'icon' => 'bi-sunset'])
                @endisset
            </dl>
        </div>
    @elseif (! $city)
        <x-empty-state icon="bi-cloud-plus-fill" title="No city selected yet">
            <x-slot:description>Add a city to start tracking its weather.</x-slot:description>

            <x-slot:action>
                <a href="{{ route('weather.cities.index') }}" class="btn-primary mt-2">
                    <i class="bi bi-plus-lg"></i>Add a city
                </a>
            </x-slot:action>
        </x-empty-state>
    @else
        <x-empty-state icon="bi-cloud-slash-fill" title="Weather data unavailable">
            <x-slot:description>The stored snapshot could not be refreshed. Try again later.</x-slot:description>
        </x-empty-state>
    @endif
@endsection
