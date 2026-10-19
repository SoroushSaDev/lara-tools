<?php

namespace App\Providers;

use App\Contracts\HolidayProvider;
use App\Contracts\SettingRepository;
use App\Contracts\WeatherService;
use App\Services\OpenWeatherService;
use App\Services\PnlHolidayProvider;
use App\Services\SettingService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces to their concrete implementations so controllers
        // and models depend on abstractions rather than implementations.
        // Settings are read on almost every request, hence the singleton.
        $this->app->singleton(SettingRepository::class, SettingService::class);
        $this->app->singleton(WeatherService::class, OpenWeatherService::class);
        $this->app->singleton(HolidayProvider::class, PnlHolidayProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ($this->viewSettings() as $name => $value) {
            View::share($name, $value);
        }
    }

    /**
     * Preferences the views read directly, keyed by their Blade variable.
     *
     * Anything that changes a query is read in its own controller instead.
     *
     * @return array<string, mixed>
     */
    protected function viewSettings(): array
    {
        // Preferences live in the database; tolerate a not-yet-migrated
        // database so artisan bootstrap (e.g. package discovery) still works.
        try {
            $settings = $this->app->make(SettingRepository::class);
        } catch (\Throwable) {
            $settings = null;
        }

        $text = fn (string $key, string $default): string => (string) ($settings?->get($key) ?? $default);
        $on = fn (string $key, bool $default = true): bool => ($settings?->get($key) ?? ($default ? '1' : '0')) === '1';

        return [
            'weatherUnits' => $text('weather_units', (string) config('services.openweather.units', 'metric')),
            'appearance' => $text('appearance', 'dark'),
            'clockTimeFormat' => $text('clock_time_format', '24h'),
            'clockShowSeconds' => $on('clock_show_seconds'),
            'clockShowAnalog' => $on('clock_show_analog'),
            'converterPrecision' => (int) ($settings?->get('converter_precision') ?? 2),
            'musicShuffleDefault' => $on('music_shuffle_default', false),
            'musicShowTags' => $on('music_show_tags'),
            'notesToolbarCompact' => $on('notes_toolbar_compact', false),
            'weatherShowTechnical' => $on('weather_show_technical'),
            'calendarShowLocations' => $on('calendar_show_locations'),
            'confirmDeletes' => $on('confirm_deletes'),
        ];
    }
}
