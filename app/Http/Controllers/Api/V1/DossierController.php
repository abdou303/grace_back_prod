<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDossierRequest;
use App\Http\Resources\DossierResource;
use App\Models\Affaire;
use App\Models\Detenu;
use App\Models\Dossier;
use App\Models\Pj;
use App\Models\Requette;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DossierController extends Controller
{
    /**
     * Display a listing of the resource.
     */

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
            'typedossier'
        ])->where('user_tribunal_id', $tr_id)->get();

        return new DossierResource($dossiers);
    }
    public function index()
    {
        //





        /*$dossiers = Dossier::with(['detenu', 'affaires', 'categoriedossier', 'naturedossier', 'typemotifdossier', 'typedossier'])->get();


        // return response()->json($dossiers);

        return new DossierResource($dossiers);*/
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
            'typedossier'
        ])->get();

        return new DossierResource($dossiers);
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDossierRequest $request)
    {
        //



        $detenu = new Detenu();
        $detenu->nom = $request->nom;
        $detenu->prenom = $request->prenom;
        $detenu->datenaissance = $request->datenaissance;
        $detenu->nompere = $request->nompere;
        $detenu->nommere = $request->nommere;
        $detenu->cin = $request->cin;
        $detenu->genre = $request->genre;
        $detenu->nationalite_id = $request->nationalite;
        $detenu->save();

        $dossier = new Dossier();

        $dossier->typedossier_id = $request->typedossier;
        $dossier->naturedossiers_id = $request->naturedossier;
        $dossier->sourcedemande_id = $request->sourcedemande;
        //$dossier->objetdemande_id = $request->objetdemande;
        //$dossier->objetdemande_id = $request->objetdemande ?? null;
        $dossier->objetdemande_id = isset($request->objetdemande) && is_numeric($request->objetdemande)  ? (int) $request->objetdemande : null;
        $dossier->user_id = $request->user_id;
        $dossier->user_tribunal_id = $request->tribunal_user_id;
        $dossier->user_tribunal_libelle = $request->tribunal_user_libelle;
        $dossier->detenu_id = $detenu->id;




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

        // Handle file uploads
        foreach ($fileMappings as $fieldName => $typepjId) {
            if ($request->hasFile($fieldName)) {
                $pj = new Pj();
                $pj->contenu = $request->file($fieldName)->store('uploads', 'public');
                $pj->dossier_id = $dossier_id;
                $pj->observation = "observation";
                $pj->typepj_id = $typepjId;
                $pj->save();
            }
        }






        /*
        $pj = new Pj();
        // Handle the files
        if ($request->hasFile('copie_decision')) {

            $pj->contenu = $request->file('copie_decision')->store('uploads', 'public');
            $pj->dossier_id = $dossier_id;
            $pj->observation = "observation";
            $pj->typepj_id = 5;
            $pj->save();
        }
        if ($request->hasFile('copie_cin')) {
            $dossier->copie_cin = $request->file('copie_cin')->store('uploads', 'public');
            $pj->dossier_id = $dossier_id;
            $pj->observation = "observation";
            $pj->typepj_id = 4;
            $pj->save();
        }
        if ($request->hasFile('copie_mp')) {
            $dossier->copie_mp = $request->file('copie_mp')->store('uploads', 'public');
            $pj->dossier_id = $dossier_id;
            $pj->observation = "observation";
            $pj->typepj_id = 3;
            $pj->save();
        }
        if ($request->hasFile('copie_non_recours')) {
            $dossier->copie_non_recours = $request->file('copie_non_recours')->store('uploads', 'public');
            $pj->dossier_id = $dossier_id;
            $pj->observation = "observation";
            $pj->typepj_id = 2;
            $pj->save();
        }
        if ($request->hasFile('copie_social')) {
            $dossier->copie_social = $request->file('copie_social')->store('uploads', 'public');
            $pj->dossier_id = $dossier_id;
            $pj->observation = "observation";
            $pj->typepj_id = 1;
            $pj->save();
        }*/
        $affaire = new Affaire();
        if ($request->has('affaires')) {
            $affaires = $request->affaires;
            foreach ($affaires as $affaireData) {



                $affaire->numeromp = $affaireData['numeromp'];
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
            'typedossier'
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
