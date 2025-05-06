<?php

use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::resource('notes', NoteController::class);

Route::get('/settings', [UserController::class, 'settings'])->name('settings');
Route::get('/bin', [UserController::class, 'bin'])->name('bin');
