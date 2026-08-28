<?php

namespace App\Providers;

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
}
