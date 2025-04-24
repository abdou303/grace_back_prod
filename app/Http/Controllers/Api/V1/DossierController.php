<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDossierRequest;
use App\Http\Requests\UpdateDossierRequest;
use App\Http\Resources\DossierResource;
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
            'copie_decision' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_cin' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_non_recours' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
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
        ])->where('user_tribunal_id', $tr_id)->orderBy('id', 'desc')->get();

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
    public function store(StoreDossierRequest $request)
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

                $file = $request->file($fieldName);
                $filename = $numero_dossier . $fieldName . '.' . $file->getClientOriginalExtension();
                $pj = new Pj();
                $pj->contenu =  $file->storeAs('public/uploads', $filename);
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
                $affaire->numeroaffaire = "TR-AFFAIRE";
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
                foreach ($fileMappings as $fieldName => $typepjId) {
                    if (isset($affaireData[$fieldName]) && $affaireData[$fieldName] instanceof \Illuminate\Http\UploadedFile) {
                        $file = $affaireData[$fieldName];

                        // Generate a unique filename
                        $filename = $dossier->numero . "_" . $affaire->id . "_" . $fieldName . '.' . $file->getClientOriginalExtension();

                        // Save the file in storage
                        $filePath = $file->storeAs('public/uploads', $filename);

                        // Insert into Pj table with affaire_id
                        $pj = new Pj();
                        $pj->contenu = $filePath;
                        $pj->dossier_id = $dossier->id;
                        $pj->affaire_id = $affaire->id; // Assign correct affaire_id
                        $pj->typepj_id = $typepjId;
                        //$pj->observation = ($typepjId == 5) ? 'Copie Decision' : 'Copie Non Recours';
                        $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                        $pj->save();
                    }
                }
            }
        }
        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $dossier,
        ], 201);
    }
    /******************************************************** */
    public function terminerDossierTr(UpdateDossierRequest $request, $dossier_id)
    {

        // Find the Dossier
        $dossier = Dossier::findOrFail($dossier_id);
        // Get the related Detenu
        $detenu = $dossier->detenu;

        if (!$detenu) {
            return response()->json(['message' => 'Detenu not found'], 404);
        }

        // $detenu = new Detenu();
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

        //$dossier = new Dossier();
        /*   $currentYear = now()->format('Y');
        $lastRecord = Dossier::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        //$lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0;

        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero_dossier = 'D-' . $currentYear . $newNumber;*/

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        //$dossier->numero = $numero_dossier;
        $dossier->etat = 'OK';

        //$dossier->objetdemande_id = $request->objetdemande ?? null;
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande)  ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->numeromp = $request->numeromp;


        //$dossier->detenu_id = $detenu->id;
        //$dossier->prison_id =  $request->prison;
        $dossier->prison_id = isset($request->prison) && is_numeric($request->prison)  ? (int) $request->prison : null;
        $dossier->numero_detention =  $request->numerolocal;





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
        // Loop over file mappings and handle file uploads
        foreach ($fileMappings as $fieldName => $typepjId) {
            // Check if there are files for this field



            $insertedObservation = $typepjLabels[$typepjId] ?? 'أخرى';


            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);

                // If files are an array (for multiple affaires)
                if (is_array($files)) {
                    foreach ($files as $affaireId => $file) {
                        // Process the file for each affaire
                        $filename = $dossier->numero . "_" . $dossier->id . "_" . $affaireId . "_" . $fieldName . '.' . $file->getClientOriginalExtension();
                        $pj = new Pj();
                        $pj->contenu = $file->storeAs('public/uploads', $filename);
                        $pj->dossier_id = $dossier->id;
                        // $pj->requette_id = $requette->id;
                        //$pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                        $pj->observation = $insertedObservation;


                        $pj->typepj_id = $typepjId;
                        $pj->affaire_id = $affaireId; // Save affaire_id from dynamic file key
                        $pj->save();
                    }
                } else {
                    // Single file upload (for cases where there's just one file)
                    $filename = $dossier->numero . "_" . $dossier->id . "_" . $fieldName . '.' . $files->getClientOriginalExtension();
                    $pj = new Pj();
                    $pj->contenu = $files->storeAs('public/uploads', $filename);
                    $pj->dossier_id = $dossier->id;
                    //$pj->requette_id = $requette->id;
                    //$pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                    $pj->observation = $insertedObservation;

                    $pj->typepj_id = $typepjId;
                    $pj->save();
                }
            }
        }









        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
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

        ]);

        // Update main dossier fields

        $dossier->numero_detention = $validated['numero_detention'] ?? $dossier->numero_detention;
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
