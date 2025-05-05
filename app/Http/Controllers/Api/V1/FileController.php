<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class FileController extends Controller
{
    //
    public function serve($filename)
    {
        $path = 'public/uploads/' . $filename;

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->file(storage_path('app/' . $path), [
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
