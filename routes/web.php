<?php

use App\Http\Controllers\MusicController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/settings', [UserController::class, 'settings'])->name('settings');
Route::get('/bin', [UserController::class, 'bin'])->name('bin');

Route::resource('notes', NoteController::class);

Route::prefix('/music')->name('music.')->group(function () {
    Route::get('/', [MusicController::class, 'index'])->name('index');
    Route::get('/pick', [MusicController::class, 'pickFileOrFolder'])->name('pick');
    Route::get('/stream/{file}', function ($file) {
        $path = 'music/' . $file;

        if (!Storage::exists($path)) {
            abort(404);
        }

        return response()->file(storage_path('app/' . $path));
    })->where('file', '.*')->name('stream');
});
