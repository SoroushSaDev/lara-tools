<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/bin', [UserController::class, 'bin'])->name('bin');
Route::get('/settings', [UserController::class, 'settings'])->name('settings');

Route::resource('notes', NoteController::class);
Route::resource('todos', TodoController::class);

Route::prefix('/music')->name('music.')->group(function () {
    Route::get('/', [MusicController::class, 'index'])->name('index');
    Route::get('/pick', [MusicController::class, 'pickFileOrFolder'])->name('pick');
    Route::delete('/{music:id}/delete', [MusicController::class, 'delete'])->name('delete');
    Route::get('/stream/{file}', [MusicController::class, 'stream'])->where('file', '.*')->name('stream');
});

Route::prefix('/calendar')->name('calendar.')->group(function() {
    Route::get('/', [CalendarController::class, 'index'])->name('index');
    Route::get('/create', [CalendarController::class, 'create'])->name('create');
    Route::post('/', [CalendarController::class, 'store'])->name('store');
});

Route::get('/calculator', function () {
    return view('calculator.index');
})->name('calculator');

Route::prefix('/weather')->name('weather.')->group(function () {
    Route::get('/', [WeatherController::class, 'index'])->name('index');
    Route::any('/cities', [WeatherController::class, 'cities'])->name('cities');
    Route::get('/coordinates/{lat}/{lon}', [WeatherController::class, 'byCoordinates']);
    Route::get('/city/{city:id}', [WeatherController::class, 'byCity']);
    Route::get('/{city:id}/data', [WeatherController::class, 'data'])->name('data');
    Route::get('/{city:id}/set', [WeatherController::class, 'homeTown'])->name('set');
    Route::get('/{city:id}/update', [WeatherController::class, 'update'])->name('update');
    Route::get('/{city:id}/delete', [WeatherController::class, 'delete'])->name('delete');
});
