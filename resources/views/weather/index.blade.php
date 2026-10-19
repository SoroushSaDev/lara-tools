@extends('layouts.main')

@section('title', 'Weather')

@php
    $unitSymbol = temperatureUnitSymbol($weatherUnits);
    $isImperial = $weatherUnits === 'imperial';

    // Payload reads go through data_get() so partial responses never throw;
    // null results hide the matching property.
    $g = fn (string $key) => data_get($weather, $key);

    $speed = fn ($v) => filled($v) && is_numeric($v)
        ? number_format((float) $v, 1).' '.($isImperial ? 'mph' : 'm/s')
        : null;

    $distance = fn ($meters) => $isImperial
        ? number_format($meters / 1609.344, $meters / 1609.344 < 10 ? 2 : 1).' mi'
        : number_format($meters / 1000, $meters / 1000 < 10 ? 1 : 0).' km';

    $compassPoints = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
    $compass = fn ($deg) => $compassPoints[(((int) round(fmod((float) $deg, 360) / 22.5)) % 16 + 16) % 16];

    // Sunrise/sunset/observation timestamps are UTC; shift by the payload's
    // offset (seconds) to get the city's local wall-clock time.
    $tzSeconds = is_numeric($g('timezone')) ? (int) $g('timezone') : 0;
    $tzLabel = is_numeric($g('timezone'))
        ? sprintf('%s%02d:%02d', $tzSeconds < 0 ? '-' : '+', intdiv(abs($tzSeconds), 3600), intdiv(abs($tzSeconds) % 3600, 60))
        : null;
    $clock = fn ($ts, string $format = 'H:i') => filled($ts) && is_numeric($ts) ? date($format, (int) $ts + $tzSeconds) : null;

    $conditions = is_array($g('weather')) ? $g('weather')
        : ($city && (filled($city->main) || filled($city->description))
            ? [['main' => $city->main, 'description' => $city->description, 'icon' => $city->icon]]
            : []);

    $temp = is_numeric($g('main.temp')) ? round((float) $g('main.temp')).$unitSymbol : null;
    $feelsLike = is_numeric($g('main.feels_like')) ? round((float) $g('main.feels_like')).$unitSymbol : null;

    [$min, $max] = [is_numeric($g('main.temp_min')) ? round((float) $g('main.temp_min')) : null, is_numeric($g('main.temp_max')) ? round((float) $g('main.temp_max')) : null];
    $tempRange = $min !== null && $max !== null ? "{$min}° / {$max}°"
        : ($max !== null ? "up to {$max}{$unitSymbol}" : ($min !== null ? "down to {$min}{$unitSymbol}" : null));

    $humidity = is_numeric($g('main.humidity')) ? (int) $g('main.humidity').'%' : null;
    $pressure = is_numeric($g('main.pressure')) ? round((float) $g('main.pressure')).' hPa' : null;

    $seaLevel = is_numeric($g('main.sea_level')) ? [round((float) $g('main.sea_level')).' hPa', null]
        : ($city && is_numeric($city->sea_level) && $city->sea_level > 0 ? [round((float) $city->sea_level).' hPa', 'stored snapshot'] : null);
    $groundLevel = is_numeric($g('main.grnd_level')) ? round((float) $g('main.grnd_level')).' hPa' : null;

    $visibility = is_numeric($g('visibility')) ? [$distance((int) $g('visibility')), (int) $g('visibility').' m'] : null;

    $windSpeed = $speed($g('wind.speed'));
    $windDeg = is_numeric($g('wind.deg')) ? [round(fmod((float) $g('wind.deg'), 360)).'°', $compass($g('wind.deg'))] : null;
    $windGust = $speed($g('wind.gust'));

    $clouds = [];
    foreach (['all' => 'Cloud cover', 'today' => 'Clouds (day)', 'night' => 'Clouds (night)'] as $key => $label) {
        if (is_numeric($g("clouds.{$key}"))) {
            $clouds[] = [$label, (int) $g("clouds.{$key}").'%'];
        }
    }

    $sunrise = $clock($g('sys.sunrise')) ?? $city?->sunrise?->format('H:i');
    $sunset = $clock($g('sys.sunset')) ?? $city?->sunset?->format('H:i');
    $sunriseFrom = $sunrise !== null && ! is_numeric($g('sys.sunrise')) ? 'stored snapshot' : null;
    $sunsetFrom = $sunset !== null && ! is_numeric($g('sys.sunset')) ? 'stored snapshot' : null;

    $observedAt = $clock($g('dt'), 'D H:i');
    $observedHint = is_numeric($g('dt')) ? date('j M Y', (int) $g('dt') + $tzSeconds) : null;

    $lat = is_numeric($g('coord.lat')) ? number_format((float) $g('coord.lat'), 4) : ($city && data_get($city->coordinates, 'lat') !== null ? [number_format((float) data_get($city->coordinates, 'lat'), 4), 'stored snapshot'] : null);
    $lon = is_numeric($g('coord.lon')) ? number_format((float) $g('coord.lon'), 4) : ($city && data_get($city->coordinates, 'lon') !== null ? [number_format((float) data_get($city->coordinates, 'lon'), 4), 'stored snapshot'] : null);
    $country = filled($g('sys.country')) ? mb_strtoupper((string) $g('sys.country')) : ($city?->country_code ? [$city->country_code, 'stored snapshot'] : null);
    $sysType = is_numeric($g('sys.type')) ? (string) $g('sys.type') : null;

    $conditionIds = collect($conditions)->pluck('id')->filter()->implode(', ');

    // Values that carry a provenance hint are [value, hint] pairs. Closures
    // are named so they never collide with variables @include passes down.
    $val = fn ($v) => is_array($v) ? $v[0] : $v;
    $prov = fn ($v) => is_array($v) ? ($v[1] ?? null) : null;
@endphp

@section('content')
    <x-page-header back="{{ route('home') }}" back-label="Home">
        <x-slot:title>Weather</x-slot:title>

        @if ($city)
            <x-slot:subtitle>
                {{ $city->isHomeTown() ? 'Your home town · ' : '' }}{{ $val($country) }}
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

    @if (is_array($weather) && $weather !== [])
        {{-- Condition hero --}}
        <div class="mb-6 flex flex-col items-center gap-5 animate-fade-up sm:flex-row sm:gap-8 lg:justify-center">
            <div class="relative shrink-0 text-center">
                <i class="bi {{ weatherIcon($conditions[0]['main'] ?? null) }} bg-gradient-to-br from-sky-400 to-blue-600 bg-clip-text text-[7rem] leading-none text-transparent drop-shadow-lg sm:text-[9rem]"></i>
                <span class="absolute inset-0 -z-10 mx-auto mt-6 size-24 rounded-full bg-sky-400/25 blur-2xl"></span>
            </div>

            <div class="min-w-0 text-center sm:text-left">
                @if (filled($temp))
                    <h2 class="heading text-6xl sm:text-7xl">{{ $temp }}</h2>
                @endif

                @if (filled($conditions[0]['description'] ?? null))
                    <p class="mt-1 text-xl font-bold capitalize text-slate-600 dark:text-slate-300">
                        {{ $conditions[0]['description'] }}
                    </p>
                @endif

                <p class="mt-2 flex items-center justify-center gap-2 text-lg font-bold sm:justify-start">
                    @if ($isLiveLocation || ($city?->name) !== ($g('name') ?? null))
                        <i class="bi bi-geo-alt-fill text-sky-500"></i>
                    @endif
                    {{ $g('name') ?: ($city?->name ?? '—') }}
                    @if (! $isLiveLocation && $city && $city->isHomeTown())
                        <i class="bi bi-star-fill text-sm text-amber-400" title="Home town"></i>
                    @endif
                </p>

                @if ($conditions !== [])
                    <div class="mt-3 flex flex-wrap justify-center gap-2 sm:justify-start">
                        @foreach ($conditions as $condition)
                            <span class="chip bg-sky-500/15 text-sky-600 dark:text-sky-300"
                                  title="ID {{ data_get($condition, 'id') ?: '—' }} · icon {{ data_get($condition, 'icon') ?: '—' }}">
                                <i class="bi {{ weatherIcon(data_get($condition, 'main')) }}"></i>
                                {{ data_get($condition, 'main') ?: '—' }}
                                <span class="font-normal lowercase text-slate-500 dark:text-slate-400">{{ data_get($condition, 'description') }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Grouped detail cards, full width --}}
        <div class="grid items-start gap-4 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3">
            <section class="glass p-5 animate-fade-up" style="animation-delay: 60ms">
                <h3 class="heading mb-4 flex items-center gap-2 text-base">
                    <i class="bi bi-thermometer-sun text-sky-500"></i>At a glance
                </h3>

                <dl class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    @includeWhen(filled($feelsLike), 'weather.partial.property', ['label' => 'Feels like', 'value' => $feelsLike, 'icon' => 'bi-thermometer-half', 'tone' => 'rose'])
                    @includeWhen(filled($tempRange), 'weather.partial.property', ['label' => 'Min / Max', 'value' => $tempRange, 'icon' => 'bi-thermometer-snow', 'tone' => 'violet'])
                    @includeWhen(filled($humidity), 'weather.partial.property', ['label' => 'Humidity', 'value' => $humidity, 'icon' => 'bi-droplet-half', 'tone' => 'emerald'])
                    @includeWhen(filled($pressure), 'weather.partial.property', ['label' => 'Pressure', 'value' => $pressure, 'icon' => 'bi-speedometer'])
                    @includeWhen($seaLevel !== null, 'weather.partial.property', ['label' => 'Sea level', 'value' => $val($seaLevel), 'hint' => $prov($seaLevel), 'icon' => 'bi-water'])
                    @includeWhen(filled($groundLevel), 'weather.partial.property', ['label' => 'Ground level', 'value' => $groundLevel, 'icon' => 'bi-hdd-rack', 'tone' => 'slate'])
                    @includeWhen($visibility !== null, 'weather.partial.property', ['label' => 'Visibility', 'value' => $val($visibility), 'hint' => $prov($visibility), 'icon' => 'bi-eye', 'tone' => 'emerald'])
                </dl>
            </section>

            <section class="glass p-5 animate-fade-up" style="animation-delay: 120ms">
                <h3 class="heading mb-4 flex items-center gap-2 text-base">
                    <i class="bi bi-wind text-sky-500"></i>Wind & sky
                </h3>

                <dl class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    @includeWhen(filled($windSpeed), 'weather.partial.property', ['label' => 'Wind speed', 'value' => $windSpeed, 'icon' => 'bi-wind'])
                    @includeWhen($windDeg !== null, 'weather.partial.property', ['label' => 'Wind direction', 'value' => $val($windDeg), 'hint' => $prov($windDeg), 'icon' => 'bi-compass'])
                    @includeWhen(filled($windGust), 'weather.partial.property', ['label' => 'Gusts', 'value' => $windGust, 'icon' => 'bi-tornado', 'tone' => 'violet'])
                    @foreach ($clouds as [$label, $value])
                        @include('weather.partial.property', ['label' => $label, 'value' => $value, 'icon' => 'bi-clouds', 'tone' => 'slate'])
                    @endforeach
                </dl>
            </section>

            <section class="glass p-5 animate-fade-up" style="animation-delay: 180ms">
                <h3 class="heading mb-4 flex items-center gap-2 text-base">
                    <i class="bi bi-sunrise text-amber-500"></i>Sun & daylight
                </h3>

                <dl class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    @includeWhen(filled($sunrise), 'weather.partial.property', ['label' => 'Sunrise', 'value' => $sunrise, 'hint' => $sunriseFrom, 'icon' => 'bi-sunrise', 'tone' => 'amber'])
                    @includeWhen(filled($sunset), 'weather.partial.property', ['label' => 'Sunset', 'value' => $sunset, 'hint' => $sunsetFrom, 'icon' => 'bi-sunset', 'tone' => 'amber'])
                    @includeWhen(filled($observedAt), 'weather.partial.property', ['label' => 'Observed at', 'value' => $observedAt, 'hint' => $observedHint, 'icon' => 'bi-clock-history'])
                    @includeWhen(filled($tzLabel), 'weather.partial.property', ['label' => 'UTC offset', 'value' => "UTC{$tzLabel}", 'icon' => 'bi-globe-americas', 'tone' => 'violet'])
                </dl>
            </section>

            <section class="glass p-5 animate-fade-up" style="animation-delay: 240ms">
                <h3 class="heading mb-4 flex items-center gap-2 text-base">
                    <i class="bi bi-geo-alt text-emerald-500"></i>Location
                </h3>

                <dl class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                    @includeWhen($lat !== null, 'weather.partial.property', ['label' => 'Latitude', 'value' => $val($lat), 'hint' => $prov($lat), 'icon' => 'bi-arrow-up-short'])
                    @includeWhen($lon !== null, 'weather.partial.property', ['label' => 'Longitude', 'value' => $val($lon), 'hint' => $prov($lon), 'icon' => 'bi-arrow-right-short'])
                    @includeWhen($country !== null, 'weather.partial.property', ['label' => 'Country', 'value' => $val($country), 'hint' => $prov($country), 'icon' => 'bi-flag', 'tone' => 'emerald'])
                    @includeWhen(filled($sysType), 'weather.partial.property', ['label' => 'System type', 'value' => $sysType, 'icon' => 'bi-tag', 'tone' => 'slate'])
                </dl>
            </section>

            @if ($weatherShowTechnical)
                <section class="glass p-5 animate-fade-up lg:col-span-2 xl:col-span-1" style="animation-delay: 300ms">
                    <h3 class="heading mb-4 flex items-center gap-2 text-base">
                        <i class="bi bi-info-circle text-slate-400"></i>Technical
                    </h3>

                    <dl class="grid grid-cols-1 gap-x-5 gap-y-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-2">
                        @includeWhen(is_numeric($g('id')), 'weather.partial.property', ['label' => 'City ID', 'value' => (string) $g('id'), 'icon' => 'bi-hash', 'tone' => 'slate'])
                        @includeWhen(is_numeric($g('sys.id')), 'weather.partial.property', ['label' => 'System record ID', 'value' => (string) $g('sys.id'), 'icon' => 'bi-fingerprint', 'tone' => 'slate'])
                        @includeWhen(filled($g('station_id')), 'weather.partial.property', ['label' => 'Station ID', 'value' => (string) $g('station_id'), 'icon' => 'bi-broadcast', 'tone' => 'slate'])
                        @includeWhen(filled($g('name')), 'weather.partial.property', ['label' => 'API name', 'value' => (string) $g('name'), 'icon' => 'bi-building', 'tone' => 'slate'])
                        @includeWhen(filled($g('base')), 'weather.partial.property', ['label' => 'Data source', 'value' => (string) $g('base'), 'icon' => 'bi-diagram-3', 'tone' => 'slate'])
                        @includeWhen(filled($g('cod')), 'weather.partial.property', ['label' => 'Response code', 'value' => (string) $g('cod'), 'icon' => 'bi-check-circle', 'tone' => 'slate'])
                        @includeWhen(filled($conditionIds), 'weather.partial.property', ['label' => 'Condition IDs', 'value' => $conditionIds, 'icon' => 'bi-list-columns', 'tone' => 'slate'])
                        @includeWhen(filled($visibility), 'weather.partial.property', ['label' => 'Raw visibility', 'value' => (int) $g('visibility').' m', 'icon' => 'bi-rulers', 'tone' => 'slate'])
                    </dl>
                </section>
            @endif
        </div>
    @elseif (! $city && ! $isLiveLocation)
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
