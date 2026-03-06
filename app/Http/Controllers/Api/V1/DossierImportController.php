<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Imports\DossierImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class DossierImportController extends Controller
{
    /* Excel::import(new DossierImport, $request->file('file'));

        return back()->with('success', 'Dossiers and Affaires imported successfully!');*/
    /*public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);


        try {
            Excel::import(new DossierImport, $request->file('file'));
            return response()->json([
                'message' => 'تم استيراد الملفات بنجاح !!!!'
            ], 200); // 200 OK with message 
        } catch (\Exception $e) {
            return back()->with('error', 'هناك خطأ في الاستيراد: ' . $e->getMessage());
        }
    }*/
    /*public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv',
    ]);

    try {
        Excel::import(new DossierImport, $request->file('file'));
        return response()->json([
            'message' => 'تم استيراد الملفات بنجاح !!!!'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'هناك خطأ في الاستيراد: ' . $e->getMessage()
        ], 500);
    }
}*/

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        // On démarre la transaction globale
        DB::beginTransaction();

        try {
            Excel::import(new DossierImport, $request->file('file'));

            // Si on arrive ici, tout s'est bien passé
            DB::commit();

            return response()->json([
                'message' => 'تم استيراد الملفات بنجاح !!!!'
            ], 200);
        } catch (\Exception $e) {
            // En cas d'erreur, on annule tout (évite les erreurs de rollback SQL Server)
            DB::rollBack();

            return response()->json([
                'error' => 'هناك خطأ في الاستيراد: ' . $e->getMessage(),
                'details' => 'Ligne: ' . $e->getLine()
            ], 500);
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
