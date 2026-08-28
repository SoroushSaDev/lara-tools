<?php

/**
 * Application-wide helper functions.
 */
if (! function_exists('formatTime')) {
    /**
     * Format a number of seconds as m:ss (e.g. 215 -> "3:35").
     *
     * @param  bool  $withHours  Include an hours segment when true.
     */
    function formatTime(int|string|null $seconds, bool $withHours = false): string
    {
        if (! is_numeric($seconds)) {
            return '--:--';
        }

        $seconds = max(0, (int) $seconds);

        if ($withHours || $seconds >= 3600) {
            return sprintf('%d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}

if (! function_exists('weatherIcon')) {
    /**
     * Map an OpenWeatherMap condition name to a Bootstrap icon class.
     *
     * @param  string|null  $condition  e.g. "Clear", "Rain", "Thunderstorm".
     */
    function weatherIcon(?string $condition): string
    {
        return match ($condition) {
            'Clear' => 'bi-sun-fill',
            'Clouds' => 'bi-clouds-fill',
            'Rain' => 'bi-cloud-rain-heavy-fill',
            'Drizzle' => 'bi-cloud-drizzle-fill',
            'Thunderstorm' => 'bi-cloud-lightning-rain-fill',
            'Snow' => 'bi-snow',
            'Mist', 'Fog', 'Haze', 'Dust', 'Sand', 'Ash', 'Squall', 'Smoke' => 'bi-cloud-haze2-fill',
            default => 'bi-cloud-sun-fill',
        };
    }
}

if (! function_exists('temperatureUnitSymbol')) {
    /**
     * Symbol for the currently configured temperature unit.
     *
     * @param  string|null  $units  "metric" or "imperial".
     */
    function temperatureUnitSymbol(?string $units = null): string
    {
        return $units === 'imperial' ? '°F' : '°C';
    }
}
