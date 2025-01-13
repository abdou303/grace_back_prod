<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DossierResource;
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
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
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
    public function store(Request $request)
    {
        //
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
            'comportement',
            'affaires',
            'requettes',
            'affaires.tribunal',
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
