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
        // Preferences live in the database; tolerate a not-yet-migrated
        // database so artisan bootstrap (e.g. package discovery) still works.
        try {
            $settings = $this->app->make(SettingRepository::class);

            View::share('weatherUnits', $settings->get('weather_units')
                ?? config('services.openweather.units', 'metric'));
            View::share('appearance', $settings->get('appearance') ?? 'dark');
            View::share('clockTimeFormat', $settings->get('clock_time_format') ?? '24h');
            View::share('clockShowSeconds', ($settings->get('clock_show_seconds') ?? '1') === '1');
            View::share('converterPrecision', (int) ($settings->get('converter_precision') ?? 2));
            View::share('musicShuffleDefault', ($settings->get('music_shuffle_default') ?? '0') === '1');
        } catch (\Throwable) {
            View::share('weatherUnits', config('services.openweather.units', 'metric'));
            View::share('appearance', 'dark');
            View::share('clockTimeFormat', '24h');
            View::share('clockShowSeconds', true);
            View::share('converterPrecision', 2);
            View::share('musicShuffleDefault', false);
        }
    }
}
