<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home.index');
})->name('home');

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

Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

Route::get('/calculator', function () {
    return view('calculator.index');
})->name('calculator');

Route::prefix('/weather')->name('weather.')->group(function () {
    Route::get('/', [WeatherController::class, 'index'])->name('index');
    Route::get('/data', [WeatherController::class, 'data'])->name('data');
    Route::get('/city/{city}', [WeatherController::class, 'byCity']);
    Route::get('/coordinates/{lat}/{lon}', [WeatherController::class, 'byCoordinates']);
});
