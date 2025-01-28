<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TribunalResource;
use App\Models\Tribunal;
use Illuminate\Http\Request;

class TribunalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $tribunaux = Tribunal::where('type_tribunal', 'C')->get();
        return TribunalResource::collection($tribunaux);

        //return TribunalResource::collection(Tribunal::all());
    }


    public function getByCa($ca_id)
    {
        // Fetch Tribunaux by ca_id
        $tribunaux = Tribunal::where('ca_id', $ca_id)->get();

        // Return the response
        return TribunalResource::collection($tribunaux);
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
