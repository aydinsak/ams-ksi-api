<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\RefCityController;
use App\Http\Controllers\API\RefProvinceController;
use App\Http\Controllers\API\Penilaian_Resiko\RiskAssessmentController;
use App\Http\Controllers\API\Penilaian_Resiko\RiskAssessmentDetailController;
use App\Http\Controllers\API\PKAT\TransRkiaController;
use App\Http\Controllers\API\RencanaBiayaController;
use App\Http\Controllers\API\Dokumen_PKAT\TransRkiaDocumentController;
use App\Http\Controllers\API\SuratPemberitahuanController;
use App\Http\Controllers\API\ProgramAuditController;

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

    // Penilaian Risiko (Risk Assessment)
    Route::get('/risk-assessment',        [RiskAssessmentController::class, 'index']);
    Route::get('/risk-assessment/{id}',   [RiskAssessmentController::class, 'show']);
    Route::post('/risk-assessment',        [RiskAssessmentController::class, 'store']);
    Route::match(['put', 'patch'], '/risk-assessment/{id}', [RiskAssessmentController::class, 'update']);
    Route::delete('/risk-assessment/{id}',   [RiskAssessmentController::class, 'destroy']);

    // penilaian risiko details (Risk Assessment Details)
    Route::prefix('risk-assessment/{riskRegisterId}/detail')->group(function () {
        Route::get('/',                 [RiskAssessmentDetailController::class, 'index']);
        Route::post('/',                [RiskAssessmentDetailController::class, 'store']);
        Route::get('/{detailId}',       [RiskAssessmentDetailController::class, 'show']);
        Route::match(['put', 'patch'], '/{detailId}', [RiskAssessmentDetailController::class, 'update']);
        Route::delete('/{detailId}',    [RiskAssessmentDetailController::class, 'destroy']);
    });

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

    Route::get('/surat-pemberitahuan',        [SuratPemberitahuanController::class, 'index']);
    Route::get('/surat-pemberitahuan/{id}',   [SuratPemberitahuanController::class, 'show']);

    Route::get('/program-audit',        [ProgramAuditController::class, 'index']);
    Route::get('/program-audit/{id}',   [ProgramAuditController::class, 'show']);
});
