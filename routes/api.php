<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;


Route::middleware('auth:api')->group(function () {
    Route::get('/me', [UserController::class, 'me']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
