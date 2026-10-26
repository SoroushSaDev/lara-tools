<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClockController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\RecycleBinController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| LaraTools is a single-user desktop application, so there is no
| authentication layer. Routes are grouped by feature and every state
| changing action uses the appropriate HTTP verb.
|
*/

// Dashboard.
Route::get('/', [HomeController::class, 'index'])->name('home');

// One term across notes, todo lists and the music library.
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Notes (full resource).
Route::resource('notes', NoteController::class);

// Todo lists + per-item completion toggles.
Route::resource('todos', TodoController::class);
Route::patch('todos/{todo}/items/{index}', [TodoController::class, 'toggleItem'])
    ->whereNumber('index')
    ->name('todos.items.toggle');

// Calendar events.
Route::prefix('calendar')->name('calendar.')->group(function () {
    Route::get('/', [CalendarController::class, 'index'])->name('index');
    Route::get('/create', [CalendarController::class, 'create'])->name('create');
    Route::post('/', [CalendarController::class, 'store'])->name('store');
    Route::get('/{event}/edit', [CalendarController::class, 'edit'])->name('edit');
    Route::put('/{event}', [CalendarController::class, 'update'])->name('update');
    Route::delete('/{event}', [CalendarController::class, 'destroy'])->name('destroy');
});

// Music library.
Route::prefix('music')->name('music.')->group(function () {
    Route::get('/', [MusicController::class, 'index'])->name('index');
    Route::post('/pick', [MusicController::class, 'pick'])->name('pick');
    Route::put('/{music}', [MusicController::class, 'update'])->name('update');
    Route::patch('/{music}/like', [MusicController::class, 'toggleLike'])->name('like');
    Route::get('/{music}/edit', [MusicController::class, 'edit'])->name('edit');
    Route::delete('/{music}/delete', [MusicController::class, 'destroy'])->name('destroy');
    Route::get('/stream/{music}', [MusicController::class, 'stream'])->name('stream');
    Route::get('/cover/{music}', [MusicController::class, 'cover'])->name('cover');
});

// Standalone calculator page.
Route::view('/calculator', 'calculator.index')->name('calculator');

// Clock tool (real-time clock, stopwatch, timers, world clocks).
Route::get('/clock', [ClockController::class, 'index'])->name('clock');
Route::post('/clock/zones', [ClockController::class, 'saveZones'])->name('clock.zones');

// Unit converter.
Route::view('/converter', 'converter.index')->name('converter');

// Weather dashboard and saved cities.
Route::prefix('weather')->name('weather.')->group(function () {
    Route::get('/', [WeatherController::class, 'index'])->name('index');
    Route::get('/locate', [WeatherController::class, 'locate'])->name('locate');
    Route::get('/cities', [WeatherController::class, 'cities'])->name('cities.index');
    Route::post('/cities', [WeatherController::class, 'storeCity'])->name('cities.store');
    Route::post('/{city}/set', [WeatherController::class, 'setHomeTown'])->name('set');
    Route::post('/{city}/refresh', [WeatherController::class, 'refresh'])->name('refresh');
    Route::delete('/{city}', [WeatherController::class, 'destroy'])->name('destroy');
});

// Application preferences.
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

// About page with version history.
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Recycle bin for soft-deleted records.
Route::prefix('bin')->name('bin.')->group(function () {
    Route::get('/', [RecycleBinController::class, 'index'])->name('index');
    Route::post('/{type}/{id}/restore', [RecycleBinController::class, 'restore'])->whereNumber('id')->name('restore');
    Route::delete('/{type}/{id}', [RecycleBinController::class, 'destroy'])->whereNumber('id')->name('destroy');
    Route::post('/empty', [RecycleBinController::class, 'empty'])->name('empty');
});
