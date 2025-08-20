<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\RefCityController;
use App\Http\Controllers\API\RefProvinceController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    //auth
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/logout',   [AuthController::class, 'logout']);

    // City routes
    Route::get('/cities', [RefCityController::class, 'index']);
    Route::get('/cities/{id}', [RefCityController::class, 'show']);

    // Province routes
    Route::get('/provinces', [RefProvinceController::class, 'index']);
    Route::get('/provinces/{id}', [RefProvinceController::class, 'show']);


    // User management routes (admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
