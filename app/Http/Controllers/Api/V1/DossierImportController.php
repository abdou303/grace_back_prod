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
            'file'               => 'required|mimes:xlsx,csv',
            'import_user_id'     => 'required',
            'import_tribunal_id' => 'nullable',
        ]);

        // Créer le log AVANT l'import
        $importLog = \App\Models\ImportLog::create([
            'nomdufichier' => $request->file('file')->getClientOriginalName(),
            'date'         => now(),
            'statut'       => 'EN_COURS',
            'user_id'      => $request->import_user_id,
            'tribunal_id'  => $request->import_tribunal_id,
        ]);

        DB::beginTransaction();

        try {
            $importer = new DossierImport($request->import_user_id, $request->import_tribunal_id, $importLog->id);
            Excel::import($importer, $request->file('file'));
            DB::commit();

            return response()->json([
                'message'             => 'تم استيراد الملفات بنجاح !!!!',
                'nomdufichier'        => $importLog->nomdufichier,
                'nb_lignes_total'     => $importer->nbTotal,
                'nb_lignes_importees' => $importer->nbImportees,
                'nb_lignes_ignorees'  => $importer->nbTotal - $importer->nbImportees,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            // Marquer le log comme échoué
            $importLog->update([
                'statut'         => 'ECHEC',
                'message_erreur' => $e->getMessage(),
            ]);

            return response()->json([
                'error'   => 'هناك خطأ في الاستيراد: ' . $e->getMessage(),
                'details' => 'Ligne: ' . $e->getLine()
            ], 500);
        }
    }
    public function historique(Request $request)
    {
        $query = \App\Models\ImportLog::orderBy('date', 'desc');

        if ($request->tribunal_id) $query->where('tribunal_id', $request->tribunal_id);
        if ($request->user_id)     $query->where('user_id', $request->user_id);

        return response()->json($query->paginate(20), 200);
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
