<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\RefCityController;
use App\Http\Controllers\API\RefProvinceController;
use App\Http\Controllers\API\RiskAssessmentController;
use App\Http\Controllers\API\TransRkiaController;
use App\Http\Controllers\API\RencanaBiayaController;
use App\Http\Controllers\API\TransRkiaDocumentController;

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
    Route::get('/risk-assessment',        [RiskAssessmentController::class, 'index']);
    Route::get('/risk-assessment/{id}',   [RiskAssessmentController::class, 'show']);
    Route::post('/risk-assessment',        [RiskAssessmentController::class, 'store']);
    Route::match(['put', 'patch'], '/risk-assessment/{id}', [RiskAssessmentController::class, 'update']);
    Route::delete('/risk-assessment/{id}',   [RiskAssessmentController::class, 'destroy']);

    // PKAT (RKIA)
    Route::apiResource('pkat', TransRkiaController::class);

    // Rencana Biaya (Rencana Biaya Audit)
    Route::apiResource('rencana-biaya', RencanaBiayaController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::prefix('rencana-biaya/{id}')->group(function () {
        // details
        Route::post('/details',                [RencanaBiayaController::class, 'addDetail']);
        Route::patch('/details/{detailId}',     [RencanaBiayaController::class, 'updateDetail']);
        Route::delete('/details/{detailId}',     [RencanaBiayaController::class, 'deleteDetail']);
        // aktiva
        Route::post('/aktiva',                 [RencanaBiayaController::class, 'addAktiva']);
        Route::patch('/aktiva/{rowId}',         [RencanaBiayaController::class, 'updateAktiva']);
        Route::delete('/aktiva/{rowId}',         [RencanaBiayaController::class, 'deleteAktiva']);
    });

    // PKAT Documents
    Route::apiResource('dokumen-rencana', TransRkiaDocumentController::class);
});
