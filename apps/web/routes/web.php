<?php

use App\Http\Controllers\SpaceController;
use App\Http\Controllers\SpaceFileController;
use App\Http\Controllers\SpaceNoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpaceController::class, 'index'])->name('spaces.index');
Route::post('/spaces', [SpaceController::class, 'store'])->name('spaces.store');
Route::get('/spaces/{space}', [SpaceController::class, 'show'])->name('spaces.show');
Route::post('/spaces/{space}/files', [SpaceFileController::class, 'store'])->name('spaces.files.store');
Route::get('/spaces/{space}/files/{file}', [SpaceFileController::class, 'show'])
    ->where('file', '[^/]+')
    ->name('spaces.files.show');
Route::post('/spaces/{space}/notes', [SpaceNoteController::class, 'store'])->name('spaces.notes.store');
