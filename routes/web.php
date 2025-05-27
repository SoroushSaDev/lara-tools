<?php

use App\Http\Controllers\MusicController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use Carbon\Carbon;
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

Route::get('/calendar', function () {
    $now = Carbon::now();
    return view('calendar.index', compact('now'));
})->name('calendar');

Route::get('/calculator', function () {
    return view('calculator.index');
})->name('calculator');
