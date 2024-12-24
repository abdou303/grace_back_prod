<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CaController;
use App\Http\Controllers\Api\V1\DossierController;
use App\Http\Controllers\Api\V1\NationaliteController;
use App\Http\Controllers\Api\V1\PaysController;
use App\Http\Controllers\Api\V1\PrisonController;
use App\Http\Controllers\Api\V1\ProfessionController;
use App\Http\Controllers\Api\V1\TribunalController;
use App\Http\Controllers\Api\V1\VilleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    Route::apiResource('villes', VilleController::class);
    Route::apiResource('cas', CaController::class);
    Route::apiResource('tribunaux', TribunalController::class);
    Route::apiResource('prisons', PrisonController::class);
    Route::apiResource('pays', PaysController::class);
    Route::apiResource('nationalites', NationaliteController::class);
    Route::apiResource('professions', ProfessionController::class);
    Route::apiResource('dossiers', DossierController::class);

    Route::post('/login', [AuthController::class, 'login']);
});
