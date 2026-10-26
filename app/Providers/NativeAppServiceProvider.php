<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Native\Laravel\Contracts\ProvidesPhpIni;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\Window;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->prepareDatabase();

        Window::open()
            ->width(1280)
            ->height(820)
            ->minWidth(380)
            ->minHeight(520);

        Menu::create(
            Menu::make(
                Menu::route('home', 'Dashboard'),
                Menu::separator(),
                Menu::route('notes.index', 'Notes'),
                Menu::route('todos.index', 'Todo Lists'),
                Menu::route('calendar.index', 'Calendar'),
                Menu::route('music.index', 'Music Player'),
                Menu::route('calculator', 'Calculator'),
                Menu::route('clock', 'Clock'),
                Menu::route('converter', 'Unit Converter'),
                Menu::route('weather.index', 'Weather'),
                Menu::separator(),
                Menu::route('bin.index', 'Recycle Bin'),
                Menu::route('settings', 'Settings'),
                Menu::route('about', 'About'),
            )->label('Navigate'),
        );
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [];
    }

    /**
     * Make sure the local database exists and is at the current schema.
     *
     * NativePHP only does this when `app.debug` is on, and a packaged app
     * never has it on, so an installed app would otherwise meet a missing
     * file or a stale schema on its very first window. Merging is
     * idempotent: an up-to-date install costs one SELECT.
     */
    public function prepareDatabase(): void
    {
        $connection = config('database.default');
        $path = config("database.connections.{$connection}.database");

        // A named connection that is not a file (:memory:, MySQL…) needs no
        // file to create, but still has to be migrated.
        if (is_string($path) && $path !== '' && ! str_starts_with($path, ':')) {
            $directory = dirname($path);

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (! file_exists($path)) {
                touch($path);
            }
        }

        try {
            Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        } catch (\Throwable $exception) {
            // Never let a schema problem stop the window from opening: the
            // logs are the only way to see what happened, and a crash on
            // launch leaves the user with nothing to look at.
            report($exception);
        }
    }
}
