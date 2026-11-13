<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MotifGreffe;

class MotifGreffeController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => MotifGreffe::orderByRaw("CASE WHEN code = 'AUTRE' THEN 1 ELSE 0 END")
                ->orderBy('id')
                ->get(),
        ]);
    }
}
