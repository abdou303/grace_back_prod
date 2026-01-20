<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Affaire;
use Illuminate\Http\Request;

class AffaireController extends Controller
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

        $affaire = Affaire::findOrFail($id);

        $request->validate([
            'numero' => 'required|string',
            'code' => 'required|string',
            'annee' => 'required|string',
            'datejujement' => 'required|date',
            'conenujugement' => 'required|string',
            'tribunal_id' => 'required|integer|exists:tribunaux,id',

        ]);

        $affaire->update([
            'numero' => $request->numero,
            'code' => $request->code,
            'annee' => $request->annee,
            'datejujement' => $request->datejujement,
            'conenujugement' => $request->conenujugement,
            'numeroaffaire' => $request->numero . '/' . $request->code . '/' . $request->annee,
            'tribunal_id' => $request->tribunal_id

        ]);

        return response()->json($affaire);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
