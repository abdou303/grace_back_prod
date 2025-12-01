<?php

use App\Http\Controllers\Api\V1\AffaireController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CaController;
use App\Http\Controllers\Api\V1\DossierController;
use App\Http\Controllers\Api\V1\NationaliteController;
use App\Http\Controllers\Api\V1\PaysController;
use App\Http\Controllers\Api\V1\PrisonController;
use App\Http\Controllers\Api\V1\ProfessionController;
use App\Http\Controllers\Api\V1\TribunalController;
use App\Http\Controllers\Api\V1\VilleController;
use App\Http\Controllers\Api\V1\TypeRequetteController;
use App\Http\Controllers\Api\V1\PartenaireController;
use App\Http\Controllers\Api\V1\RequetteController;
use App\Http\Controllers\Api\V1\AvisController;
use App\Http\Controllers\Api\V1\ComportementController;
use App\Http\Controllers\Api\V1\DossierImportController;
use App\Http\Controllers\Api\V1\FichePdfController;
use App\Http\Controllers\Api\V1\FileController;
use App\Http\Controllers\Api\V1\NatureDossierController;
use App\Http\Controllers\Api\V1\ObjetDemandeController;
use App\Http\Controllers\Api\V1\ProvinceController;
use App\Http\Controllers\Api\V1\SourceDemandeController;
use App\Http\Controllers\Api\V1\StatisticsController;
use App\Http\Controllers\Api\V1\TypeDossierController;
use App\Http\Controllers\Api\V1\TypeMotifDossierController;
use App\Http\Controllers\Api\V1\OpenBeeController;



use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

/*Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('dossiers', DossierController::class);
});*/

/*Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::prefix('v1')->group(function () {

    Route::apiResource('villes', VilleController::class);
    Route::apiResource('cas', CaController::class);
    Route::apiResource('tribunaux', TribunalController::class);
    Route::apiResource('prisons', PrisonController::class);
    Route::apiResource('pays', PaysController::class);
    Route::apiResource('nationalites', NationaliteController::class);
    Route::apiResource('professions', ProfessionController::class);
    Route::apiResource('dossiers', DossierController::class);
    Route::apiResource('affaires', AffaireController::class);
    Route::apiResource('typesrequettes', TypeRequetteController::class);
    Route::apiResource('partenaires', PartenaireController::class);
    Route::apiResource('requettes', RequetteController::class);
    Route::apiResource('avis', AvisController::class);
    Route::apiResource('comportements', ComportementController::class);
    Route::apiResource('provinces', ProvinceController::class);
    Route::apiResource('typesdossiers', TypeDossierController::class);
    Route::apiResource('naturesdossiers', NatureDossierController::class);
    Route::apiResource('typesmotifsdossiers', TypeMotifDossierController::class);
    Route::apiResource('objetsdemandes', ObjetDemandeController::class);
    Route::apiResource('sourcesdemandes', SourceDemandeController::class);
    Route::get('/requettes/dossier/{dossier_id}', [RequetteController::class, 'getByDossier']);
    Route::get('/dossiers-tribunaux', [DossierController::class, 'dossiersTr']);
    Route::get('/dossiers-dapg', [DossierController::class, 'dossiersDapg']);
    Route::get('/tribunaux/ca/{ca_id}', [TribunalController::class, 'getByCa']);
    Route::post('/import-dossiers', [DossierImportController::class, 'import']);
    Route::get('/dossiers/tribunal/{tr_id}', [DossierController::class, 'dossierByTr']);
    Route::get('/requettes/tribunal/{tr_id}', [RequetteController::class, 'requetteByTr']);
    Route::get('/requettes-nt/tribunal/{tr_id}', [RequetteController::class, 'requetteNTByTr']);
    Route::get('/nt-requettes', [RequetteController::class, 'getNTRequettes']);
    Route::get('/tr-requettes', [RequetteController::class, 'getTRRequettes']);
    Route::put('/requettes/{requette}/change-statut', [RequetteController::class, 'changeStatut']);
    Route::put('/requettes/{requette}', [RequetteController::class, 'confirmRequette']);
    Route::post('/requettes/reponse-tr/{requette_id}', [RequetteController::class, 'addReponseRequette']);
    Route::post('/dossiers/terminer-tr/{dossier_id}', [DossierController::class, 'terminerDossierTr']);
    Route::post('/antecedent-dossiers', [DossierController::class, 'storeAntecedent']);
    Route::get('/dossier/{id}/pdf', [FichePdfController::class, 'generatePdf']);
    Route::get('/dossiers/{id}/pjs', [DossierController::class, 'getPjs']);
    Route::post('/login', [AuthController::class, 'login']);
});*/


Route::prefix('v1')->group(function () {
       

    // Public route
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/openbee/file/{id}', [OpenBeeController::class, 'download']);


    // Protected routes
    Route::middleware(['auth:api'])->group(function () {

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::apiResource('villes', VilleController::class);
        Route::apiResource('cas', CaController::class);
        Route::apiResource('tribunaux', TribunalController::class);
        Route::apiResource('prisons', PrisonController::class);
        Route::apiResource('pays', PaysController::class);
        Route::apiResource('nationalites', NationaliteController::class);
        Route::apiResource('professions', ProfessionController::class);
        Route::apiResource('affaires', AffaireController::class);
        Route::apiResource('typesrequettes', TypeRequetteController::class);
        Route::apiResource('partenaires', PartenaireController::class);
        Route::apiResource('requettes', RequetteController::class);
        Route::apiResource('avis', AvisController::class);
        Route::apiResource('comportements', ComportementController::class);
        Route::apiResource('provinces', ProvinceController::class);
        Route::apiResource('typesdossiers', TypeDossierController::class);
        Route::apiResource('naturesdossiers', NatureDossierController::class);
        Route::apiResource('typesmotifsdossiers', TypeMotifDossierController::class);
        Route::apiResource('objetsdemandes', ObjetDemandeController::class);
        Route::apiResource('sourcesdemandes', SourceDemandeController::class);
		Route::apiResource('dossiers', DossierController::class);
        Route::get('/requettes/dossier/{dossier_id}', [RequetteController::class, 'getByDossier']);
        Route::get('/dossiers-tribunaux', [DossierController::class, 'dossiersTr']);
        Route::get('/dossiers-dapg', [DossierController::class, 'dossiersDapg']);
        Route::get('/tribunaux/ca/{ca_id}', [TribunalController::class, 'getByCa']);
        Route::post('/import-dossiers', [DossierImportController::class, 'import']);
        Route::get('/dossiers/tribunal/{tr_id}', [DossierController::class, 'dossierByTr']);
        Route::get('/requettes/tribunal/{tr_id}', [RequetteController::class, 'requetteByTr']);
        Route::get('/requettes-nt/tribunal/{tr_id}', [RequetteController::class, 'requetteNTByTr']);
        Route::get('/nt-requettes', [RequetteController::class, 'getNTRequettes']);
        Route::get('/tr-requettes', [RequetteController::class, 'getTRRequettes']);
        Route::put('/requettes/{requette}/change-statut', [RequetteController::class, 'changeStatut']);
        Route::put('/requettes/{requette}', [RequetteController::class, 'confirmRequette']);
        Route::post('/requettes/reponse-tr/{requette_id}', [RequetteController::class, 'addReponseRequette']);
        Route::post('/dossiers/terminer-tr/{dossier_id}', [DossierController::class, 'terminerDossierTr']);
        Route::post('/antecedent-dossiers', [DossierController::class, 'storeAntecedent']);
        Route::get('/dossier/{id}/pdf', [FichePdfController::class, 'generatePdf']);
        Route::get('/dossiers/{id}/pjs', [DossierController::class, 'getPjs']);
		Route::get('/requettes/{id}/pjs', [RequetteController::class, 'getPjs']);
        Route::get('/statistiques', [StatisticsController::class, 'getDossierStats']);
        Route::get('/statistiques/{tr_id}', [StatisticsController::class, 'getDossierStatsByTR']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/openbee/file/{id}', [OpenBeeController::class, 'download']);
    });
});
