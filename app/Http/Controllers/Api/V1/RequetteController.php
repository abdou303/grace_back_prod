<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGreffeRequetteRequest;
use App\Http\Requests\UpdateRequetteRequest;
use App\Http\Resources\RequetteResource;
use App\Jobs\UploadDossierPJsJob;
use App\Models\Pj;
use App\Models\Requette;
use App\Models\StatutRequette;
use App\Models\TypePj;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Services\OpenBeeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequetteController extends Controller
{


    /* public function addReponseRequette(UpdateRequetteRequest $request, $requette_id,OpenBeeService $openBee)
    {
        // Find the Requette
        $requette = Requette::findOrFail($requette_id);
        // Get the related Dossier
        $dossier = $requette->dossier;

        if (!$dossier) {
            return response()->json(['message' => 'Dossier not found'], 404);
        }

        $dossier->numeromp = $request->numeromp;
        $dossier->save();

  

        $fileMappings = [
            'copie_cat2' => 6,
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];


        // Fetch all TypePj records and create an associative array of id => label
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();


        // Loop over file mappings and handle file uploads
        foreach ($fileMappings as $fieldName => $typepjId) {
            // Check if there are files for this field


            $insertedObservation = "";
            if ($requette->typerequette->cat == "CAT-1") {
                $insertedObservation = $typepjLabels[$typepjId] ?? 'أخرى';
            } else {
                $insertedObservation = $requette->typerequette->libelle ?? 'أخرى';
            }

            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);

                // If files are an array (for multiple affaires)
                if (is_array($files)) {
                    foreach ($files as $affaireId => $file) {
                        // Process the file for each affaire
                        //$pj->contenu = $file->storeAs('public/uploads', $filename);
                        $filename = $requette->numero . "_" . $dossier->id . "_" . $affaireId . "_" . $fieldName . '.' . $file->getClientOriginalExtension();
                        $filenameSansExtension = pathinfo($filename, PATHINFO_FILENAME);

                        $path="OPENBEE/".$filename;
                        try {
                            $openBee->deleteIfExists($filenameSansExtension);
                            $result = $openBee->upload($file, $filename, [
                                'title'       => $filename,
                                'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                                'path'        => config('openbee.path'),
                            ]);
                            $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;
                        } catch (\Exception $e) {
                            \Log::error("Erreur d'upload Open Bee (sans affaire): " . $e->getMessage());
                            $openbeeUrl = null;
                        }
        
                        $pj = new Pj();
                        //$pj->contenu = $file->storeAs('public/uploads', $filename);
                        $pj->contenu = $path;
                        $pj->openbee_url = $openbeeUrl;
                        $pj->dossier_id = $dossier->id;
                        $pj->requette_id = $requette->id;
                        //$pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                        $pj->observation = $insertedObservation;


                        $pj->typepj_id = $typepjId;
                        $pj->affaire_id = $affaireId; // Save affaire_id from dynamic file key
                        $pj->save();
                    }
                } else {
                    // Single file upload (for cases where there's just one file)
                    $filename = $requette->numero . "_" . $dossier->id . "_" . $fieldName . '.' . $files->getClientOriginalExtension();
                    $filenameSansExtension = pathinfo($filename, PATHINFO_FILENAME);

                    $path="OPENBEE/".$filename;
                    try {
                        $openBee->deleteIfExists($filenameSansExtension);
                        $result = $openBee->upload($files, $filename, [
                            'title'       => $filename,
                            'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                            'path'        => config('openbee.path'),
                        ]);
                        $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;
                    } catch (\Exception $e) {
                        \Log::error("Erreur d'upload Open Bee (sans affaire): " . $e->getMessage());
                        $openbeeUrl = null;
                    }
                    $pj = new Pj();
                    //$pj->contenu = $files->storeAs('public/uploads', $filename);
                    $pj->contenu = $path;

                    $pj->dossier_id = $dossier->id;
                    $pj->requette_id = $requette->id;
                    //$pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                    $pj->observation = $insertedObservation;
                    $pj->openbee_url = $openbeeUrl;
                    $pj->typepj_id = $typepjId;
                    $pj->save();
                }
            }
        }

        // Update StatutRequette
        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        if ($request->statutRequette == 'OK') {

            $requette->etat_tribunal = 'TR';
            $requette->save();
        }
        $requette->statutrequettes()->attach([$id_staut]);

        return response()->json(['message' => 'Statut updated successfully', 'requette' => $requette->load('statutrequettes')]);
    }*/

    public function addReponseRequette(UpdateRequetteRequest $request, $requette_id)
    {
        // L'injection de OpenBeeService n'est plus nécessaire ici.

        // 1. Logique métier immédiate (Base de données)
        $requette = Requette::findOrFail($requette_id);
        $dossier = $requette->dossier;

        if (!$dossier) {
            return response()->json(['message' => 'Dossier not found'], 404);
        }

        $dossier->numeromp = $request->numeromp;
        $dossier->save();

        // 2. Préparation et Stockage TEMPORAIRE des fichiers (Logique similaire à terminerDossierTr)
        $filesToProcess = [];
        $fileMappings = [
            'copie_cat2' => 6,
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];

        foreach ($fileMappings as $fieldName => $typepjId) {
            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);
                $filesArray = is_array($files) ? $files : [null => $files];

                foreach ($filesArray as $affaireIdKey => $file) {
                    if ($file) {
                        // **Stockage temporaire**
                        $path = $file->store('temp/openbee_uploads');
                        $filesToProcess[] = [
                            'path' => $path,
                            'typepjId' => $typepjId,
                            'affaireId' => is_numeric($affaireIdKey) ? (int) $affaireIdKey : null,
                            'fieldName' => $fieldName,
                            'originalName' => $file->getClientOriginalName(),
                            // DONNÉE CLÉ pour le Job : Indiquer l'ID de la Requette
                            'context_requette_id' => $requette->id,
                        ];
                    }
                }
            }
        }
        // 2-bis. Mise à jour des AFFAIRES (non recours / cassation)
        if ($request->has('has_non_recours')) {
            foreach ($request->has_non_recours as $affaireId => $hasNonRecours) {

                $affaire = $dossier->affaires()
                    ->where('affaires.id', $affaireId)
                    ->first();

                if (!$affaire) {
                    continue;
                }

                $hasNonRecoursBool = filter_var($hasNonRecours, FILTER_VALIDATE_BOOLEAN);

                $affaire->has_non_recours = $hasNonRecoursBool;

                if (!$hasNonRecoursBool) {
                    $affaire->numero_cassation = $request->numero_cassation[$affaireId] ?? null;
                    $affaire->numero_envoi_cassation = $request->numero_envoi_cassation[$affaireId] ?? null;
                    $affaire->date_envoi_cassation = $request->date_envoi_cassation[$affaireId] ?? null;
                } else {
                    $affaire->numero_cassation = null;
                    $affaire->numero_envoi_cassation = null;
                    $affaire->date_envoi_cassation = null;
                }

                $affaire->save();
            }
        }

        // 3. Dispatch du Job
        if (!empty($filesToProcess)) {
            // L'appel reste identique à celui de terminerDossierTr
            UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess)->onQueue('openbee_uploads');
        }

        // 4. Finalisation des statuts (Logique métier immédiate)
        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        if ($request->statutRequette == 'OK') {
            $requette->etat_tribunal = 'TR';
            $requette->date_etat_tribunal = now()->format('Y-m-d H:i:s.v');
            $requette->user_tribunal = $request->user_tribunal;
            $requette->dossier()->update([
                'etat' => 'OK',
                'tr_tribunal' => 'OK',

            ]);
            $requette->save();
        }
        $requette->statutrequettes()->attach([$id_staut]);

        // 5. Réponse Immédiate
        return response()->json([
            'message' => 'Statut mis à jour. L\'upload des documents a démarré en arrière-plan.',
            'requette' => $requette->load('statutrequettes')
        ], 200);
    }

    public function addReponseGreffeRequette(UpdateGreffeRequetteRequest $request, $requette_id)
    {
        // L'injection de OpenBeeService n'est plus nécessaire ici.

        // 1. Logique métier immédiate (Base de données)
        $requette = Requette::findOrFail($requette_id);
        $dossier = $requette->dossier;

        if (!$dossier) {
            return response()->json(['message' => 'Dossier not found'], 404);
        }

        $dossier->numeromp = $request->numeromp;
        $dossier->save();

        // 2. Préparation et Stockage TEMPORAIRE des fichiers (Logique similaire à terminerDossierTr)
        $filesToProcess = [];
        $fileMappings = [
            'copie_cat2' => 6,
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];

        foreach ($fileMappings as $fieldName => $typepjId) {
            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);
                $filesArray = is_array($files) ? $files : [null => $files];

                foreach ($filesArray as $affaireIdKey => $file) {
                    if ($file) {
                        // **Stockage temporaire**
                        $path = $file->store('temp/openbee_uploads');
                        $filesToProcess[] = [
                            'path' => $path,
                            'typepjId' => $typepjId,
                            'affaireId' => is_numeric($affaireIdKey) ? (int) $affaireIdKey : null,
                            'fieldName' => $fieldName,
                            'originalName' => $file->getClientOriginalName(),
                            // DONNÉE CLÉ pour le Job : Indiquer l'ID de la Requette
                            'context_requette_id' => $requette->id,
                        ];
                    }
                }
            }
        }
        // 2-bis. Mise à jour des AFFAIRES (non recours / cassation)
        if ($request->has('has_non_recours')) {
            foreach ($request->has_non_recours as $affaireId => $hasNonRecours) {

                $affaire = $dossier->affaires()
                    ->where('affaires.id', $affaireId)
                    ->first();

                if (!$affaire) {
                    continue;
                }

                $hasNonRecoursBool = filter_var($hasNonRecours, FILTER_VALIDATE_BOOLEAN);

                $affaire->has_non_recours = $hasNonRecoursBool;

                if (!$hasNonRecoursBool) {
                    $affaire->numero_cassation = $request->numero_cassation[$affaireId] ?? null;
                    $affaire->numero_envoi_cassation = $request->numero_envoi_cassation[$affaireId] ?? null;
                    $affaire->date_envoi_cassation = $request->date_envoi_cassation[$affaireId] ?? null;
                } else {
                    $affaire->numero_cassation = null;
                    $affaire->numero_envoi_cassation = null;
                    $affaire->date_envoi_cassation = null;
                }

                $affaire->save();
            }
        }
        // 3. Dispatch du Job
        if (!empty($filesToProcess)) {
            // L'appel reste identique à celui de terminerDossierTr
            UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess)->onQueue('openbee_uploads');
        }

        // 4. Finalisation des statuts (Logique métier immédiate)
        /*$id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        if ($request->statutRequette == 'OK') {
            $requette->etat_tribunal = 'TR';
            $requette->date_etat_tribunal = now()->format('Y-m-d H:i:s.v');
            $requette->user_tribunal = $request->user_tribunal;

            $requette->save();
        }
        $requette->statutrequettes()->attach([$id_staut]);*/

        $requette->etat_greffe = 'TR';
        $requette->date_etat_greffe = now()->format('Y-m-d H:i:s.v');
        $requette->user_greffe = $request->user_tribunal;

        $requette->save();


        // 5. Réponse Immédiate
        return response()->json([
            'message' => 'Statut mis à jour. L\'upload des documents a démarré en arrière-plan.',
            'requette' => $requette->load('statutrequettes')
        ], 200);
    }
    /*
    public function addReponseRequette(UpdateRequetteRequest $request, $requette_id)
    {

        // Find the Requette
        $requette = Requette::findOrFail($requette_id);
        // Get the related Dossier
        $dossier = $requette->dossier;

        if (!$dossier) {
            return response()->json(['message' => 'Dossier not found'], 404);
        }

        $dossier->numeromp = $request->numeromp;
        $dossier->save();


        // Define file fields and corresponding typepj_id values
        $fileMappings = [
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];
        // Fetch all TypePj records and create an associative array of id => label
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();
        foreach ($fileMappings as $fieldName => $typepjId) {



            //echo "********".$fieldName."*************";

            if ($request->hasFile($fieldName)) {

                $file = $request->file($fieldName);

                $filename = $dossier->numero_dossier . $fieldName . '.' . $file->getClientOriginalExtension();
                $pj = new Pj();
                $pj->contenu =  $file->storeAs('public/uploads', $filename);
                $pj->dossier_id = $dossier->id;
                $pj->requette_id = $requette->id;
                $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                $pj->typepj_id = $typepjId;
                $pj->save();
            }
        }

        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        $requette->statutrequettes()->sync([$id_staut]);

        return response()->json(['message' => 'Statut updated successfully', 'requette' => $requette->load('statutrequettes')]);
    }
*/

    public function changeStatut(Request $request, Requette $requette)
    {
        $request->validate([
            'statutRequette' => 'required|exists:statut_requettes,code',
        ]);
        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        $requette->statutrequettes()->attach([$id_staut]);

        return response()->json(['message' => 'Statut updated successfully', 'requette' => $requette->load('statutrequettes')]);
    }


    public function confirmRequette(Request $request, Requette $requette)
    {


        $data = $request->validate([
            'date' => 'nullable',
            'observations' => 'nullable|string',
            'dossier_id' => 'required|int',
            'user_id' => 'required|int',
            'tribunal_id' => 'required|int',
            'typerequette_id' => 'required|int',
            'copie_demande' => 'nullable|file|mimes:pdf|max:2048', // Validation du fichier
        ]);

        //Log::debug('************ Requête reçue (forwardRequette) :*******************', $request->all());

        /*
        $currentYear = now()->format('Y');
        $lastRecord = Requette::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0; // Adjusted substring index
        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = 'R-' . $currentYear . $newNumber;
        */
        $currentYear = now()->format('Y');

        // Find the last numero starting with the current year
        $lastNumero = Requette::where('numero', 'like', "R-$currentYear%")
            ->orderBy('numero', 'desc')
            ->value('numero');

        if ($lastNumero) {
            // Extract numeric part after "R-YYYY"
            $lastNumber = intval(substr($lastNumero, 6));
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = 'R-' . $currentYear . $newNumber;

        // Add the generated "numero" to the validated data
        $requette->fill($data);
        $requette->numero = $numero;
        $requette->etat = "TR";
        $requette->etat_greffe = "KO";
        $requette->etat_parquet = "KO";

        $requette->save();
        //   Mise à jour du dossier lié
        $requette->dossier()->update([
            'etat' => 'NT',
            'tr_tribunal' => 'NT',
            'user_tribunal_id' => $request->tribunal_id,
            'categorie' => $requette->typerequette->cat
        ]);
        $id_staut = StatutRequette::where('code', 'KO')->value('id');
        $requette->statutrequettes()->attach($id_staut);

        // 3. Préparation et Stockage TEMPORAIRE des fichiers
        $filesToProcess = [];
        $fileMappings = [

            'copie_demande' => 7,
        ];

        foreach ($fileMappings as $fieldName => $typepjId) {
            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);

                // Gérer les fichiers multiples (si affaireId est la clé) ou unique
                $filesArray = is_array($files) ? $files : [null => $files];

                foreach ($filesArray as $affaireIdKey => $file) {
                    if ($file) {
                        // Stocker le fichier dans un emplacement temporaire de Laravel
                        $path = $file->store('temp/openbee_uploads');
                        $filesToProcess[] = [
                            'path' => $path, // Chemin d'accès temporaire
                            'typepjId' => $typepjId,
                            'affaireId' => is_numeric($affaireIdKey) ? (int) $affaireIdKey : null,
                            'fieldName' => $fieldName,
                            'originalName' => $file->getClientOriginalName(),
                            // DONNÉE CLÉ pour le Job : Indiquer l'ID de la Requette
                            'context_requette_id' => $requette->id,
                        ];
                    }
                }
            }
        }



        // 4. Dispatch du Job pour le traitement en arrière-plan
        if (!empty($filesToProcess)) {
            // Le Job prendra le relai pour l'upload OpenBee et l'enregistrement Pj
            UploadDossierPJsJob::dispatch($request->dossier_id, $filesToProcess)->onQueue('openbee_uploads');
        }

        return new RequetteResource($requette);
    }

    public function forwardRequette(Request $request, Requette $requette)
    {
        $data = $request->validate([

            'observations' => 'nullable|string',
            'dossier_id' => 'required|int',
            'user_id' => 'required|int',
            'tribunal_id' => 'required|int',
            'typerequette_id' => 'required|int',
        ]);


        $requette->date_envoi_greffe = now()->format('Y-m-d H:i:s.v');
        $requette->etat_greffe = "NT";
        $requette->save();
        $id_staut = StatutRequette::where('code', 'TR')->value('id');
        $requette->statutrequettes()->attach($id_staut);




        /*
        // 5. Réponse Immédiate (C'est la clé pour éviter le timeout)
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح. يتم الآن معالجة المرفقات في الخلفية.',
            'data' => $dossier,
        ], 201);*/

        return new RequetteResource($requette);
    }

    public function forwardParquetRequette(Request $request, Requette $requette)
    {
        $data = $request->validate([


            'parquet_user_id' => 'required|int',

        ]);


        $requette->date_envoi_parquet = now()->format('Y-m-d H:i:s.v');
        $requette->etat_parquet = "NT";
        $requette->user_parquet = $request->parquet_user_id;
        $requette->save();

        $id_staut = StatutRequette::where('code', 'TR')->value('id');
        $requette->statutrequettes()->attach($id_staut);







        return new RequetteResource($requette);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $requettes = Requette::with([
            'dossier',
            'dossier.detenu',
            'dossier.detenu.profession',
            'dossier.detenu.nationalite',
            'dossier.affaires',
            'dossier.typedossier',
            'dossier.naturedossier',
            'dossier.affaires.tribunal',
            'dossier.prison',
            'dossier.requettes',
            'dossier.pjs',
            'dossier.pjs.affaire',
            'tribunal',
            'typerequette',
            'statutrequettes' => function ($query) {
                $query->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
        ])->get();

        return new RequetteResource($requettes);
    }


    public function getByDossier($dossier_id)
    {
        // Fetch Requettes by dossier_id
        $requettes = Requette::where('dossier_id', $dossier_id)
            ->with([
                'dossier',
                //'statutrequettes',
                'statutrequettes' => function ($query) {
                    $query->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
                },
                'tribunal',
                'typerequette',
                'partenaire',
                'dossier.naturedossier',
                'dossier.typedossier',
                'dossier.detenu.nationalite',
                'dossier.prison',
            ])
            ->get();

        // Return the response
        return response()->json($requettes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validatedData = $request->validate([
            'date' => 'nullable|date',
            'etat' => 'nullable',
            'partenaire' => 'nullable|string',
            'contenu' => 'required|string',
            'observations' => 'required|string',
            'dossier_id' => 'int',
            'user_id' => 'int',
            'partenaire_id' => 'int',
            'tribunal_id' => 'int',
            'typerequette_id' => 'int',
        ]);
        // Generate the "numero" value
        /*$currentYear = now()->format('Y');
        $lastRecord = Requette::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = $currentYear . $newNumber;*/
        //$numero =   "R-" . $currentYear . $newNumber;
        /*$currentYear = now()->format('Y');
        $lastRecord = Requette::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0; // Adjusted substring index
        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = 'R-' . $currentYear . $newNumber;*/
        $currentYear = now()->format('Y');

        // Find the last numero starting with the current year
        $lastNumero = Requette::where('numero', 'like', "R-$currentYear%")
            ->orderBy('numero', 'desc')
            ->value('numero');

        if ($lastNumero) {
            // Extract numeric part after "R-YYYY"
            $lastNumber = intval(substr($lastNumero, 6));
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = 'R-' . $currentYear . $newNumber;
        // Add the generated "numero" to the validated data
        $validatedData['numero'] = $numero;
        $validatedData['etat_tribunal'] = "NT";


        $requette = Requette::create($validatedData);
        $id_staut = StatutRequette::where('code', 'KO')->value('id');
        $requette->statutrequettes()->attach($id_staut);


        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $requette,
        ], 201);
    }


    public function requetteNTByTr($tr_id)
    {



        $requettes = Requette::with([
            'dossier',
            'dossier.detenu',
            'dossier.affaires',
            'userParquetObjet:id,name',
            'dossier.affaires.tribunal',
            'statutrequettes' => function ($query) {
                $query->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
            'dossier.naturedossier',
            'dossier.typedossier',
            'dossier.detenu.nationalite',
            'dossier.prison',
            'dossier.garants',
            'tribunal',
            'typerequette'
        ])->where('tribunal_id', $tr_id)->where('etat', 'TR')->where(function ($query) {
            $query->where('etat_tribunal', '!=', 'TR')
                ->orWhereNull('etat_tribunal');
        })->orderBy('requettes.numero', 'desc')->get();

        return new RequetteResource($requettes);
    }

    public function requetteByTr($tr_id)
    {



        $requettes = Requette::with([
            'dossier',
            'dossier.detenu',
            'dossier.affaires',
            'dossier.affaires.tribunal',
            'statutrequettes' => function ($query) {
                $query->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
            'dossier.naturedossier',
            'dossier.typedossier',
            'dossier.detenu.nationalite',
            'dossier.prison',
            'dossier.garants',
            'tribunal',
            'typerequette'
        ])->where('tribunal_id', $tr_id)->where('etat', 'TR')->orderBy('requettes.numero', 'desc')->get();

        return new RequetteResource($requettes);
    }


    public function getNTRequettes()
    {
        //
        $requettes = Requette::where('etat', 'NT')->with([
            'dossier',
            'dossier.detenu',
            'dossier.detenu.profession',
            'dossier.detenu.nationalite',
            'dossier.affaires',
            'dossier.typedossier',
            'dossier.naturedossier',
            'dossier.affaires.tribunal',
            'dossier.prison',
            'dossier.requettes',
            'dossier.pjs',
            'dossier.pjs.affaire',
            'tribunal',
            'typerequette',
            'statutrequettes' => function ($query) {
                $query->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
        ])->get();

        return new RequetteResource($requettes);
    }

    public function getTRRequettes()
    {
        //
        $requettes = Requette::where('etat', 'TR')->with([
            'dossier',
            'dossier.detenu',
            'dossier.detenu.profession',
            'dossier.detenu.nationalite',
            'dossier.affaires',
            'dossier.typedossier',
            'dossier.naturedossier',
            'dossier.affaires.tribunal',
            'dossier.prison',
            'dossier.requettes',
            'dossier.pjs',
            'dossier.pjs.affaire',
            'tribunal',
            'typerequette',
            'statutrequettes' => function ($query) {
                $query->orderBy('requette_statut_requette.created_at', 'desc')->limit(1);
            },
        ])->get();

        return new RequetteResource($requettes);
    }

    public function getPjs($requetteId)
    {
        $requette = Requette::with('pjs')->findOrFail($requetteId);
        return response()->json($requette->pjs);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //


        $user = Auth::user();



        // Vérifier si l'utilisateur est connecté et remplit les conditions
        // Note : Ajustez les noms des colonnes 'role' et 'group' selon votre table users
        if (!$user || $user->role_id != 3 || $user->groupe_id != 1) {
            return response()->json([
                'message' => 'غير مسموح لك بالقيام بهذا الإجراء' // "Non autorisé" en arabe
            ], 403);
        }

        $requette = Requette::find($id);

        if (!$requette) {
            return response()->json(['message' => 'الطلب غير موجود'], 404);
        }

        $requette->delete();

        return response()->json(['message' => 'تم الحذف بنجاح'], 200);
    }

    public function storeAntecedentRequette(Request $request, $requette_id)
    {

        // 1. Logique métier immédiate (Base de données)
        $requette = Requette::findOrFail($requette_id);
        $dossier = $requette->dossier;

        if (!$dossier) {
            return response()->json(['message' => 'Dossier not found'], 404);
        }

        $dossier->has_antecedent = $request->has_antecedent;
        $dossier->antecedant_id = $request->antecedant_id;
        $dossier->user_id = $request->user_id;

        $dossier->save();


        // 4. Finalisation des statuts (Logique métier immédiate)
        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        if ($request->statutRequette == 'OK') {
            $requette->etat_tribunal = 'TR';
            $requette->date_etat_tribunal = now()->format('Y-m-d H:i:s.v');
            $requette->user_tribunal = $request->user_tribunal;
            $requette->dossier()->update([
                'etat' => 'OK',
                'tr_tribunal' => 'OK',

            ]);
            $requette->save();
        }
        $requette->statutrequettes()->attach([$id_staut]);




        /*
        $requette->etat_tribunal = 'TR';
        $requette->date_etat_tribunal = now()->format('Y-m-d H:i:s.v');
        $requette->user_tribunal = $request->user_tribunal;
        $requette->dossier()->update([
            'etat' => 'OK',
            'tr_tribunal' => 'OK',

        ]);
        $requette->save();*/

        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }
}
