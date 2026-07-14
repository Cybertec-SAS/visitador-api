<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FarmContactController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FarmGeorreferenceController;
use App\Http\Controllers\GalponController;
use App\Http\Controllers\GalponSystemController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SystemsCatalogController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitPhotoController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Master data
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('farms', FarmController::class);
    Route::apiResource('farm-georreferences', FarmGeorreferenceController::class);
    Route::apiResource('farm-contacts', FarmContactController::class);
    Route::get('/farms/{farm}/galpones', [GalponController::class, 'index']);
    Route::post('/farms/{farm}/galpones', [GalponController::class, 'store']);
    Route::get('/galpones/{galpon}', [GalponController::class, 'show']);
    Route::match(['put', 'patch'], '/galpones/{galpon}', [GalponController::class, 'update']);
    Route::delete('/galpones/{galpon}', [GalponController::class, 'destroy']);
    Route::get('/galpones/{galpon}/systems', [GalponSystemController::class, 'index']);
    Route::post('/galpones/{galpon}/systems', [GalponSystemController::class, 'store']);
    Route::get('/galpon-systems/{galponSystem}', [GalponSystemController::class, 'show']);
    Route::match(['put', 'patch'], '/galpon-systems/{galponSystem}', [GalponSystemController::class, 'update']);
    Route::delete('/galpon-systems/{galponSystem}', [GalponSystemController::class, 'destroy']);

    // Catalogs
    Route::apiResource('systems-catalog', SystemsCatalogController::class);

    // Projects & progress
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.progress-reports', ProgressReportController::class)->shallow();

    // Visits
    Route::apiResource('visits', VisitController::class);
    Route::post('/visits/{visit}/fotos', [VisitPhotoController::class, 'store']);
    Route::get('/visits/{visit}/fotos/{photo}', [VisitPhotoController::class, 'show'])->name('visits.fotos.show');
    Route::delete('/visits/{visit}/fotos/{photo}', [VisitPhotoController::class, 'destroy']);
});
