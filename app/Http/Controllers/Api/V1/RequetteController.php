<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RequetteResource;
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
        $requettes = Requette::with([
            'dossier'
        ])->get();

        return new RequetteResource($requettes);
    }


    public function getByDossier($dossier_id)
    {
        // Fetch Requettes by dossier_id
        $requettes = Requette::where('dossier_id', $dossier_id)
        ->with(['dossier', 'tribunal','typerequette'])
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

        $requette = Requette::create($validatedData);

        return response()->json([
            'message' => 'Request created successfully!',
            'data' => $requette,
        ], 201);
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
