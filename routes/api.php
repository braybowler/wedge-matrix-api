<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\WedgeMatrixController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/up', function () {
    return 'Wedge Matrix API is up and ready for requests.';
})->name('up');

Route::post('/register', RegisterController::class)->name('register');
Route::post('/login', LoginController::class)->name('login');

Route::get('/wedge-matrix', [WedgeMatrixController::class, 'index'])
    ->name('wedge-matrix.index')
    ->middleware('auth:sanctum');

Route::put('/wedge-matrix', [WedgeMatrixController::class, 'update'])
    ->name('wedge-matrix.update')
    ->middleware('auth:sanctum');
