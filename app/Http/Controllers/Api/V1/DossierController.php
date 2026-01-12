<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAntecedentDossierRequest;
use App\Http\Requests\StoreDossierRequest;
use App\Http\Requests\UpdateDossierGreffeRequest;
use App\Http\Requests\UpdateDossierRequest;
use App\Http\Resources\DossierResource;
use App\Jobs\UploadDossierPJsJob;
use App\Services\OpenBeeService;
use App\Models\Affaire;
use App\Models\Detenu;
use App\Models\Dossier;
use App\Models\Pj;
use App\Models\Prison;
use App\Models\Requette;
use App\Models\StatutRequette;
use App\Models\TypePj;
use Illuminate\Support\Facades\Log; // Import Log facade

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DossierController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    public function dossierApresReponseRequette(Request $request, Requette $requette, Dossier $dossier)
    {
        $request->validate([
            'statutRequette' => 'required|exists:statut_requettes,code',
            'numeromp' => 'required',
            'copie_decision' => 'nullable|file|mimes:pdf|max:2048',
            'copie_cin' => 'nullable|file|mimes:pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:pdf|max:2048',
            'copie_non_recours' => 'nullable|file|mimes:pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:pdf|max:2048',
        ]);

        $dossier->numeromp = $request->numeromp;
        $dossier->copie_decision = $request->copie_decision;
        $dossier->copie_cin = $request->copie_cin;
        $dossier->copie_mp = $request->copie_mp;
        $dossier->copie_non_recours = $request->copie_non_recours;
        $dossier->copie_social = $request->copie_social;

        $dossier->save();


        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        $requette->statutrequettes()->attach([$id_staut]);

        return response()->json(['message' => 'Statut updated successfully', 'requette' => $requette->load('statutrequettes')]);
    }

    public function dossierByTr($tr_id)
    {
        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->where('user_tribunal_id', $tr_id)->where('categorie', 'CAT-1')->orderBy('id', 'desc')->get();

        return new DossierResource($dossiers);
    }
    public function index()
    {

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'prison',
            'objetdemande',
        ])->get();

        return new DossierResource($dossiers);
    }

    public function dossiersTr()
    {

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'prison',
            'objetdemande',
            'sourcedemande',
        ])->whereNotNull('user_tribunal_id')->orderBy('id', 'desc')
            ->get();

        return new DossierResource($dossiers);
    }

    public function dossiersDapg()
    {

        $dossiers = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
            'prison',
            'objetdemande',
        ])->whereNull('user_tribunal_id')->orderBy('id', 'desc')->get();

        return new DossierResource($dossiers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDossierRequest $request, OpenBeeService $openBee)
    {





        $detenu = new Detenu();
        $detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->adresse = $request->adresse ?? null;

        $detenu->save();

        $dossier = new Dossier();
        $currentYear = now()->format('Y');
        $lastRecord = Dossier::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        //$lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0;

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero_dossier = 'D-' . $currentYear . $newNumber;

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->autre_source = $request->autre_source;


        $dossier->numero = $numero_dossier;
        $dossier->etat = 'NT';

        //$dossier->objetdemande_id = $request->objetdemande ?? null;
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande)  ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;


        $dossier->detenu_id = $detenu->id;
        //$dossier->prison_id =  $request->prison;
        $dossier->prison_id = isset($request->prison) && is_numeric($request->prison)  ? (int) $request->prison : null;
        $dossier->numero_detention =  $request->numerolocal;
        $dossier->etat_greffe =  "NT";
        $dossier->date_envoi_greffe =  now()->format('Y-m-d H:i:s.v');







        $dossier->save();
        $dossier_id = $dossier->id;



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
        // Handle file uploads
        foreach ($fileMappings as $fieldName => $typepjId) {




            if ($request->hasFile($fieldName)) {
                $insertedObservation = "";
                $file = $request->file($fieldName);
                $filename = $numero_dossier . $fieldName . '.' . $file->getClientOriginalExtension();
                $path = "OPENBEE/" . $filename;


                try {

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
                //$pj->contenu =  $file->storeAs('public/uploads', $filename);
                $pj->contenu = $path;
                $pj->openbee_url = $openbeeUrl;
                $pj->dossier_id = $dossier_id;
                $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                $pj->typepj_id = $typepjId;
                $pj->save();
            }
        }








        if ($request->has('affaires')) {
            $affaires = $request->affaires;
            foreach ($affaires as $affaireData) {
                $affaire = new Affaire();


                //$affaire->numeromp = $affaireData['numeromp'];
                $affaire->numero = $affaireData['numero'];
                $affaire->code = $affaireData['code'];
                $affaire->annee = $affaireData['annee'];
                $affaire->numeroaffaire = "";
                $affaire->tribunal_id = $affaireData['tribunal'];
                $affaire->datejujement = $affaireData['datejujement'];
                $affaire->conenujugement = $affaireData['conenujugement'];
                $affaire->save();
                $dossier->affaires()->attach($affaire->id);

                // Handle file uploads for this affaire
                $fileMappings = [
                    'copie_decision' => 5,
                    'copie_cin' => 4,
                    'copie_mp' => 3,
                    'copie_non_recours' => 2,
                    'copie_social' => 1,
                ];
                // Fetch all TypePj records and create an associative array of id => label
                $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();

                // foreach (['copie_decision' => 5, 'copie_non_recours' => 2] as $fieldName => $typepjId) {
                /* foreach ($fileMappings as $fieldName => $typepjId) {
                    if (isset($affaireData[$fieldName]) && $affaireData[$fieldName] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $affaireData[$fieldName];

                        // Generate a unique filename
                        $filename = $dossier->numero . "_" . $affaire->id . "_" . $fieldName . '.' . $file->getClientOriginalExtension();
                        $path = "OPENBEE/" . $filename;
                        // Save the file in storage
                        //$filePath = $file->storeAs('public/uploads', $filename);
                        try {
                            //$openBee->deleteIfExists($filename);
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
                        // Insert into Pj table with affaire_id
                        $pj = new Pj();
                        $pj->contenu = $path;
                        $pj->dossier_id = $dossier->id;
                        $pj->affaire_id = $affaire->id; // Assign correct affaire_id
                        $pj->typepj_id = $typepjId;
                        $pj->openbee_url = $openbeeUrl;

                        //$pj->observation = ($typepjId == 5) ? 'Copie Decision' : 'Copie Non Recours';
                        $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                        $pj->save();
                    }

                }*/
                //UploadDossierPJsJob::dispatch($fileMappings, $affaireData, $dossier, $affaire);
            }
        }
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }



    public function storeAntecedent(StoreAntecedentDossierRequest $request)
    {


        $dossier = new Dossier();
        $currentYear = now()->format('Y');
        $lastRecord = Dossier::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        //$lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0;

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero_dossier = 'D-' . $currentYear . $newNumber;

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->numero = $numero_dossier;
        $dossier->etat = 'NT';
        $dossier->has_antecedent = $request->has_antecedent;
        $dossier->antecedant_id = $request->antecedant_id;
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande)  ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;
        $dossier->detenu_id = $request->detenu_id;

        $dossier->save();



        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }
    /******************************************************** */

    /*public function terminerDossierTr(UpdateDossierRequest $request, $dossier_id, OpenBeeService $openBee)
    {
        \Log::debug('Requête reçue :', $request->all());

        $dossier = Dossier::findOrFail($dossier_id);
        $detenu = $dossier->detenu;

        if (!$detenu) {
            return response()->json(['message' => 'Detenu not found'], 404);
        }

        $detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->adresse = $request->adresse ?? null;
        $detenu->save();

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->etat = 'OK';
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande) ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;
        $dossier->prison_id = isset($request->prison) && is_numeric($request->prison) ? (int) $request->prison : null;
        $dossier->numero_detention = $request->numerolocal;
        $dossier->save();

        $fileMappings = [
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
        ];
        $typepjLabels = TypePj::pluck('libelle', 'id')->toArray();
        
        foreach ($fileMappings as $fieldName => $typepjId) {
            $insertedObservation = $typepjLabels[$typepjId] ?? 'أخرى';

            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);

                if (is_array($files)) {
                    foreach ($files as $affaireId => $file) {
                        $filename = $dossier->numero . "_" . $dossier->id . "_" . $affaireId . "_" . $fieldName . '.' . $file->getClientOriginalExtension();
                        $filenameSansExtension = pathinfo($filename, PATHINFO_FILENAME);


                        //$path = $file->storeAs('public/uploads', $filename);
                        $path = "OPENBEE/" . $filename;

                        try {
                            $openBee->deleteIfExists($filenameSansExtension);
                            $result = $openBee->upload($file, $filename, [
                                'title'       => $filename,
                                'description' => 'تطبيق تبادل الملفات الإلكتروني للعفو والإفراج ' . $insertedObservation,
                                'path'        => config('openbee.path'),
                            ]);
                            $openbeeUrl = $result['document_link'] ?? $result['url'] ?? null;
                        } catch (\Exception $e) {
                            \Log::error("Erreur d'upload Open Bee (affaire: $affaireId): " . $e->getMessage());
                            $openbeeUrl = null;
                        }

                        $pj = Pj::firstOrNew([
                            'dossier_id' => $dossier->id,
                            'affaire_id' => $affaireId,
                            'typepj_id'  => $typepjId,
                        ]);
                        $pj->contenu = $path;
                        $pj->openbee_url = $openbeeUrl;
                        $pj->observation = $insertedObservation;
                        $pj->save();
                    }
                } else {
                    $filename = $dossier->numero . "_" . $dossier->id . "_" . $fieldName . '.' . $files->getClientOriginalExtension();
                    $filenameSansExtension = pathinfo($filename, PATHINFO_FILENAME);
                    //$path = $files->storeAs('public/uploads', $filename);
                    $path = "OPENBEE/" . $filename;


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

                    $pj = Pj::firstOrNew([
                        'dossier_id' => $dossier->id,
                        'typepj_id'  => $typepjId,
                        'affaire_id' => null
                    ]);
                    $pj->contenu = $path;
                    $pj->openbee_url = $openbeeUrl;
                    $pj->observation = $insertedObservation;
                    $pj->save();
                }
            }
        }

               
        





        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }

*/

    public function terminerDossierTr(UpdateDossierRequest $request, $dossier_id, OpenBeeService $openBee)
    {
        Log::debug('Requête reçue :', $request->all());

        $dossier = Dossier::findOrFail($dossier_id);
        $detenu = $dossier->detenu;

        if (!$detenu) {
            return response()->json(['message' => 'Detenu not found'], 404);
        }

        // 1. Mise à jour et sauvegarde IMMÉDIATE du Détenu
        $detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->adresse = $request->adresse ?? null;
        $detenu->save();

        // 2. Mise à jour et sauvegarde IMMÉDIATE du Dossier
        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->etat = 'OK'; // Mise à jour immédiate de l'état
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande) ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;
        $dossier->prison_id = isset($request->prison) && is_numeric($request->prison) ? (int) $request->prison : null;
        $dossier->numero_detention = $request->numerolocal;
        $dossier->save();

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



        // 3. Préparation et Stockage TEMPORAIRE des fichiers
        $filesToProcess = [];
        $fileMappings = [
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
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
                        ];
                    }
                }
            }
        }



        // 4. Dispatch du Job pour le traitement en arrière-plan
        if (!empty($filesToProcess)) {
            // Le Job prendra le relai pour l'upload OpenBee et l'enregistrement Pj
            UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess)->onQueue('openbee_uploads');
        }

        // 5. Réponse Immédiate (C'est la clé pour éviter le timeout)
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح. يتم الآن معالجة المرفقات في الخلفية.',
            'data' => $dossier,
        ], 201);
    }


    public function terminerGreffeDossierTr(UpdateDossierGreffeRequest $request, $dossier_id, OpenBeeService $openBee)
    {
        Log::debug('Requête reçue :', $request->all());

        $dossier = Dossier::findOrFail($dossier_id);
        $detenu = $dossier->detenu;

        if (!$detenu) {
            return response()->json(['message' => 'Detenu not found'], 404);
        }

        // 1. Mise à jour et sauvegarde IMMÉDIATE du Détenu
        /*$detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->adresse = $request->adresse ?? null;
        $detenu->save();*/

        // 2. Mise à jour et sauvegarde IMMÉDIATE du Dossier

        $dossier->etat_greffe = 'TR'; // Mise à jour immédiate de l'état
        $dossier->user_id = $request->user_id;
        $dossier->date_etat_greffe = now()->format('Y-m-d H:i:s.v');

        $dossier->save();

        // 3. Préparation et Stockage TEMPORAIRE des fichiers
        $filesToProcess = [];
        $fileMappings = [
            'copie_decision' => 5,
            'copie_cin' => 4,
            'copie_mp' => 3,
            'copie_non_recours' => 2,
            'copie_social' => 1,
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
        // 4. Dispatch du Job pour le traitement en arrière-plan
        if (!empty($filesToProcess)) {
            // Le Job prendra le relai pour l'upload OpenBee et l'enregistrement Pj
            UploadDossierPJsJob::dispatch($dossier->id, $filesToProcess)->onQueue('openbee_uploads');
        }

        // 5. Réponse Immédiate (C'est la clé pour éviter le timeout)
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح. يتم الآن معالجة المرفقات في الخلفية.',
            'data' => $dossier,
        ], 201);
    }

    /******************************************************** */
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $dossier = Dossier::with([
            'detenu',
            'detenu.profession',
            'detenu.nationalite',
            'garants',
            'garants.province',
            'garants.tribunal',
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
            'affaires.peine',
            'affaires.peine.prisons',
            'categoriedossier',
            'naturedossier',
            'sourcedemande',
            'typemotifdossier',
            'typedossier',
            'pjs',
            'pjs.requette',
            'pjs.affaire',
        ])->findOrFail($id);
        return new DossierResource($dossier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        Log::debug('Requête reçue UPDATE DOSSIER:', $request->all());

        $dossier = Dossier::with(['detenu', 'affaires', 'prison'])->findOrFail($id);

        // Validate incoming request (add rules as needed)
        $validated = $request->validate([

            'numero_detention' => 'nullable|string',
            'detenu.nom' => 'nullable|string',
            'detenu.prenom' => 'nullable|string',
            'detenu.nompere' => 'nullable|string',
            'detenu.nommere' => 'nullable|string',
            'detenu.cin' => 'nullable|string',
            'detenu.genre' => 'nullable|string',
            'detenu.datenaissance' => 'nullable|string',
            'tr_tribunal' => 'nullable|string',
            'tr_dapg' => 'nullable|string',
            'date_tr_tribunal' => 'nullable|string',
            'date_tr_dapg' => 'nullable|string',

            'user_id' => 'required|int',
            'prison' => 'nullable|int',
            'date_sortie' => 'nullable|string',






        ]);

        // Update main dossier fields

        $dossier->numero_detention = $validated['numero_detention'] ?? $dossier->numero_detention;
        $dossier->tr_tribunal = $validated['tr_tribunal'] ?? $dossier->tr_tribunal;
        $dossier->date_tr_tribunal = $validated['date_tr_tribunal'] ?? $dossier->date_tr_tribunal;

        $dossier->tr_dapg = $validated['tr_dapg'] ?? $dossier->tr_dapg;
        $dossier->date_tr_dapg = $validated['date_tr_dapg'] ?? $dossier->date_tr_dapg;
        $dossier->date_sortie = $validated['date_sortie'] ?? $dossier->date_sortie;



        /*if ($request->filled('tr_tribunal')) {
            $dossier->tr_tribunal = $validated['tr_tribunal'];
            $dossier->date_tr_tribunal = now()->format('Y-m-d H:i:s.v');
        }
        if ($request->filled('tr_dapg')) {
            $dossier->tr_dapg = $validated['tr_dapg'];
            $dossier->date_tr_dapg = now()->format('Y-m-d H:i:s.v');
        }*/



        $dossier->user_id = $validated['user_id'] ?? $dossier->user_id;
        $dossier->prison_id = $validated['prison'] ?? $dossier->prison_id;



        $dossier->save();

        // Update detenu fields
        if (isset($validated['detenu'])) {
            $dossier->detenu->update($validated['detenu']);
        }



        return response()->json([
            'message' => 'Dossier updated successfully',
            'data' => $dossier->load(['detenu', 'affaires', 'prison']),
        ]);
    }



    public function getPjs($dossierId)
    {
        $dossier = Dossier::with('pjs')->findOrFail($dossierId);
        return response()->json($dossier->pjs);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //


        //


        $user = Auth::user();



        // Vérifier si l'utilisateur est connecté et remplit les conditions
        // Note : Ajustez les noms des colonnes 'role' et 'group' selon votre table users
        if (!$user || $user->role_id != 3 || $user->groupe_id != 1) {
            return response()->json([
                'message' => 'غير مسموح لك بالقيام بهذا الإجراء' // "Non autorisé" en arabe
            ], 403);
        }

        $requette = Dossier::find($id);

        if (!$requette) {
            return response()->json(['message' => 'الطلب غير موجود'], 404);
        }

        $requette->delete();

        return response()->json(['message' => 'تم الحذف بنجاح'], 200);
    }


    public function getRegistreTribunal($id_tr)
    {
        $dossiers = Dossier::with([
            'detenu',
            'garants',
            'affaires',

            'requettes' => function ($query) {
                $query
                    ->whereNotNull('etat_greffe')
                    ->where('etat_greffe', '!=', 'KO')
                    ->whereHas('typerequette', fn($q) => $q->where('cat', 'CAT-1'))
                    ->latest('id')
                    ->limit(1)
                    ->with('typerequette');
            }
        ])
            ->where('user_tribunal_id', $id_tr)
            ->where('categorie', 'CAT-1')

            /* ⭐ LOGIQUE CORRECTE */
            ->where(function ($query) {

                // DOSSIERS AYANT AU MOINS UNE REQUETTE CAT-1 VALIDE
                $query->whereHas('requettes', function ($q) {
                    $q->whereNotNull('etat_greffe')
                        ->where('etat_greffe', '!=', 'KO')
                        ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                })

                    // OU DOSSIERS SANS AUCUNE REQUETTE CAT-1 VALIDE
                    ->orWhere(function ($q) {
                        $q->whereDoesntHave('requettes', function ($q2) {
                            $q2->whereNotNull('etat_greffe')
                                ->where('etat_greffe', '!=', 'KO')
                                ->whereHas('typerequette', fn($t) => $t->where('cat', 'CAT-1'));
                        })
                            ->whereNotNull('etat_greffe')
                            ->where('etat_greffe', '!=', 'KO');
                    });
            })

            ->orderByDesc('id')
            ->get();

        return DossierResource::collection($dossiers);
    }
}
