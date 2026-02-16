<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WedgeMatrixController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:auth')->group(function () {
    Route::get('/up', function () {
        return response()->json(['status' => 'ok']);
    });
    Route::post('/register', RegisterController::class)->name('register');
    Route::post('/login', LoginController::class)->name('login');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', UserController::class)->name('user.show');

    Route::get('/wedge-matrix', [WedgeMatrixController::class, 'index'])
        ->name('wedge-matrix.index');

    Route::put('/wedge-matrix/{wedgeMatrix}', [WedgeMatrixController::class, 'update'])
        ->name('wedge-matrix.update');
});
