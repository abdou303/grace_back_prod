<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDossierRequest;
use App\Http\Resources\DossierResource;
use App\Models\Affaire;
use App\Models\Detenu;
use App\Models\Dossier;
use App\Models\Requette;
use Illuminate\Http\Request;

class DossierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
        $detenu->nom =$request->nom;
        $detenu->prenom =$request->prenom;
        $detenu->datenaissance =$request->datenaissance;
        $detenu->nompere =$request->nompere;
        $detenu->nommere =$request->nommere;
        $detenu->cin =$request->cin;
        $detenu->genre =$request->genre;
        $detenu->nationalite =$request->nationalite;
        $detenu->save();

        $dossier = new Dossier();
        $dossier->typedossier_id =$request->typedossier;
        $dossier->naturedossiers_id =$request->naturedossiers;
        $dossier->detenu_id =$detenu->id;
        $dossier->save();
        $dossier_id = $dossier->id;


    // Handle the files
    if ($request->hasFile('copie_decision')) {
    $dossier->copie_decision = $request->file('copie_decision')->store('uploads', 'public');
    }
    $affaire = new Affaire();
    if ($request->has('affaires')) {
        $affaires = $request->affaires;
        foreach ($affaires as $affaireData) {
            
            

            $affaire->numeromp=$affaireData['numeromp'];
            $affaire->numero=$affaireData['numero'];
            $affaire->code=$affaireData['code'];
            $affaire->annee=$affaireData['annee'];
            $affaire->tribunal_id=$affaireData['tribunal'];
            $affaire->datejujement=$affaireData['datejujement'];
            $affaire->conenujugement=$affaireData['conenujugement'];
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
