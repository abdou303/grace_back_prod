<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRequetteRequest;
use App\Http\Resources\RequetteResource;
use App\Models\Pj;
use App\Models\Requette;
use App\Models\StatutRequette;
use App\Models\TypePj;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class RequetteController extends Controller
{


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

        // Loop over file mappings and handle file uploads
        foreach ($fileMappings as $fieldName => $typepjId) {
            // Check if there are files for this field
            if ($request->hasFile($fieldName)) {
                $files = $request->file($fieldName);

                // If files are an array (for multiple affaires)
                if (is_array($files)) {
                    foreach ($files as $affaireId => $file) {
                        // Process the file for each affaire
                        $filename = $dossier->numero_dossier . "_" . $affaireId . "_" . $fieldName . '.' . $file->getClientOriginalExtension();
                        $pj = new Pj();
                        $pj->contenu = $file->storeAs('public/uploads', $filename);
                        $pj->dossier_id = $dossier->id;
                        $pj->requette_id = $requette->id;
                        $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                        $pj->typepj_id = $typepjId;
                        $pj->affaire_id = $affaireId; // Save affaire_id from dynamic file key
                        $pj->save();
                    }
                } else {
                    // Single file upload (for cases where there's just one file)
                    $filename = $dossier->numero_dossier . "_" . $fieldName . '.' . $files->getClientOriginalExtension();
                    $pj = new Pj();
                    $pj->contenu = $files->storeAs('public/uploads', $filename);
                    $pj->dossier_id = $dossier->id;
                    $pj->requette_id = $requette->id;
                    $pj->observation = $typepjLabels[$typepjId] ?? 'أخرى';
                    $pj->typepj_id = $typepjId;
                    $pj->save();
                }
            }
        }

        // Update StatutRequette
        $id_staut = StatutRequette::where('code', $request->statutRequette)->value('id');
        $requette->statutrequettes()->attach([$id_staut]);

        return response()->json(['message' => 'Statut updated successfully', 'requette' => $requette->load('statutrequettes')]);
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
            'partenaire' => 'nullable|string',
            'contenu' => 'required|string',
            'observations' => 'required|string',
            'dossier_id' => 'int',
            'partenaire_id' => 'int',
            'tribunal_id' => 'int',
            'typerequette_id' => 'int'
        ]);
        // Generate the "numero" value
        /*$currentYear = now()->format('Y');
        $lastRecord = Requette::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = $currentYear . $newNumber;*/
        //$numero =   "R-" . $currentYear . $newNumber;
        $currentYear = now()->format('Y');
        $lastRecord = Requette::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 7)) : 0; // Adjusted substring index
        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero = 'R-' . $currentYear . $newNumber;

        // Add the generated "numero" to the validated data
        $validatedData['numero'] = $numero;
        $requette = Requette::create($validatedData);
        $id_staut = StatutRequette::where('code', 'KO')->value('id');
        $requette->statutrequettes()->attach($id_staut);


        return response()->json([
            'message' => 'تم تسجيل الطلب بنجاح',
            'data' => $requette,
        ], 201);
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
        ])->where('tribunal_id', $tr_id)->get();

        return new RequetteResource($requettes);
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
    public function destroy(string $id)
    {
        //
    }
}
