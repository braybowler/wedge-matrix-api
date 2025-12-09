<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/up', function () {
    return 'Wedge Matrix API is up and ready for requests.';
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

