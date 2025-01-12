<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Comportement;
use Illuminate\Http\Request;
use App\Http\Resources\ComportementResource;

class ComportementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return ComportementResource::collection(Comportement::all());
        


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
    public function show(Comportement $comportement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comportement $comportement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comportement $comportement)
    {
        //
    }
}
