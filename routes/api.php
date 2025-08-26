<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\RefCityController;
use App\Http\Controllers\API\RefProvinceController;
use App\Http\Controllers\API\RiskAssessmentController;

//auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/logout',   [AuthController::class, 'logout']);

    // User management routes (admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::post('/users', [UserController::class, 'store']);
    Route::match(['put', 'patch'], '/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

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

    // Penilaian Risiko (Risk Assessment Register CRUD)
    Route::get('/risk-assessments',        [RiskAssessmentController::class, 'index']);
    Route::get('/risk-assessments/{id}',   [RiskAssessmentController::class, 'show']);
    Route::post('/risk-assessments',        [RiskAssessmentController::class, 'store']);
    Route::match(['put', 'patch'], '/risk-assessments/{id}', [RiskAssessmentController::class, 'update']);
    Route::delete('/risk-assessments/{id}',   [RiskAssessmentController::class, 'destroy']);
});
