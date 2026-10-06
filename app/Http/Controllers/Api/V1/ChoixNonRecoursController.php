<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChoixNonRecours;

class ChoixNonRecoursController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => ChoixNonRecours::where('active', true)->orderBy('id')->get(['id', 'libelle']),
        ]);
    }
}
