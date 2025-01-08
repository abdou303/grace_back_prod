<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Requette;
use Illuminate\Http\Request;

class RequetteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'observations' => 'string',
            'dossier_id'=>'int',
            'partenaire_id'=>'integer',
            'tribunal_id'=>'integer',
            'typerequette_id'=>'int'
        ]);

        $requette = Requette::create($validatedData);

        return response()->json([
            'message' => 'Request created successfully!',
            'data' => $requette,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Requette $requette)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Requette $requette)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Requette $requette)
    {
        //
    }
}
