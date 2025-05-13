<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Imports\DossierImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DossierImportController extends Controller
{

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        /* Excel::import(new DossierImport, $request->file('file'));

        return back()->with('success', 'Dossiers and Affaires imported successfully!');*/
        try {
            Excel::import(new DossierImport, $request->file('file'));
            return response()->json([
                'message' => 'تم استيراد الملفات بنجاح !!!!'
            ], 200); // 200 OK with message 
        } catch (\Exception $e) {
            return back()->with('error', 'هناك خطأ في الاستيراد: ' . $e->getMessage());
        }
    }
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
