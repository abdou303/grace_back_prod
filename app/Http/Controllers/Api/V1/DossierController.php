<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDossierRequest;
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
        $requette->statutrequettes()->sync([$id_staut]);

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
        ])->where('user_tribunal_id', $tr_id)->get();

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
            'prison',
            'objetdemande',
        ])->whereNotNull('user_tribunal_id')->get();

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
        ])->whereNull('user_tribunal_id')->get();

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
        // $detenu->nationalite_id = $request->nationalite;
        $detenu->save();

        $dossier = new Dossier();
        $currentYear = now()->format('Y');
        $lastRecord = Dossier::whereYear('created_at', $currentYear)->orderBy('id', 'desc')->first();

        $lastNumber = $lastRecord ? intval(substr($lastRecord->numero, 4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        $numero_dossier = $currentYear . $newNumber;

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        $dossier->numero = $numero_dossier;
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



            //echo "********".$fieldName."*************";

            if ($request->hasFile($fieldName)) {

                //  echo "///////////////////".$fieldName."////////////////////";
                $file = $request->file($fieldName);

                $filename = $numero_dossier . $fieldName . '.' . $file->getClientOriginalExtension();
                $pj = new Pj();
                // $pj->contenu = $request->file($fieldName)->store('uploads', 'public');
                // $filePath = $file->storeAs('public/uploads', $filename);
                //$pj->contenu = str_replace('public/', '', $filePath); // Save relative path
                $pj->contenu =  $file->storeAs('public/uploads', $filename);
                $pj->dossier_id = $dossier_id;
                //$pj->observation = "observation";
                // Assign observation dynamically from the database
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
            }
        }
    }

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
