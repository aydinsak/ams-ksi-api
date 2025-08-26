<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\RefCityController;
use App\Http\Controllers\API\RefProvinceController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// City routes
Route::get('/cities', [RefCityController::class, 'index']);
Route::get('/cities/{id}', [RefCityController::class, 'show']);

// Province routes
Route::get('/provinces', [RefProvinceController::class, 'index']);
Route::get('/provinces/{id}', [RefProvinceController::class, 'show']);
//testing
Route::get('/provinces/{id}/cities', function ($id) {
    $cities = \App\Models\RefCity::where('province_id', $id)->orderBy('id')->get();
    return \App\Http\Resources\mini\CityMiniResource::collection($cities);
});

Route::middleware('auth:api')->group(function () {
    //auth
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/logout',   [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);

    // User management routes (admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
});
