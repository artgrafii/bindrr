<?php

use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Api\SpaceFileController;
use Illuminate\Support\Facades\Route;

Route::get('/spaces', [SpaceController::class, 'index']);
Route::get('/spaces/{space}/files', [SpaceFileController::class, 'index']);
Route::get('/spaces/{space}/files/{file}', [SpaceFileController::class, 'show'])->where('file', '[^/]+');
Route::put('/spaces/{space}/files/{file}', [SpaceFileController::class, 'update'])->where('file', '[^/]+');
