<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TypeRequette;
use Illuminate\Http\Request;
use App\Http\Resources\TypeRequetteResource;

class TypeRequetteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return TypeRequetteResource::collection(TypeRequette::all());
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
    public function show(TypeRequette $typeRequette)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TypeRequette $typeRequette)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TypeRequette $typeRequette)
    {
        //
    }
}
