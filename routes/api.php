<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\FarmContactController;
use App\Http\Controllers\FarmController;
use App\Http\Controllers\FarmGeorreferenceController;
use App\Http\Controllers\ProgressReportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\SystemsCatalogController;
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
    Route::apiResource('structures', StructureController::class);

    // Catalogs
    Route::apiResource('systems-catalog', SystemsCatalogController::class);

    // Projects & progress
    Route::apiResource('projects', ProjectController::class);
    Route::get('projects/{project}/structures', [StructureController::class, 'indexByProject']);
    Route::post('projects/{project}/structures', [StructureController::class, 'syncProjectStructures']);
    Route::delete('projects/{project}/structures/{structure}', [StructureController::class, 'detachFromProject']);
    Route::apiResource('projects.progress-reports', ProgressReportController::class)->shallow();
});
