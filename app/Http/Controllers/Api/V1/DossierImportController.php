<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Imports\DossierImport;
use App\Imports\DossierImportEncours;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class DossierImportController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Import classique (avec Requettes, originedossier = 'R')
    // ─────────────────────────────────────────────────────────────────────────
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
            $importer = new DossierImport(
                $request->import_user_id,
                $request->import_tribunal_id,
                $importLog->id
            );
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

            $importLog->update([
                'statut'         => 'ECHEC',
                'message_erreur' => $e->getMessage(),
            ]);

            return response()->json([
                'error'   => 'هناك خطأ في الاستيراد: ' . $e->getMessage(),
                'details' => 'Ligne: ' . $e->getLine(),
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

    // ─────────────────────────────────────────────────────────────────────────
    // Import "ملفات رائجة" (sans Requettes, originedossier = 'DAPG-ENCOURS')
    // ─────────────────────────────────────────────────────────────────────────
    public function importEncours(Request $request)
    {
        $request->validate([
            'file'               => 'required|mimes:xlsx,csv',
            'import_user_id'     => 'required',
            'import_tribunal_id' => 'nullable',
        ]);

        $importLog = \App\Models\ImportEncoursLog::create([
            'nomdufichier' => $request->file('file')->getClientOriginalName(),
            'date'         => now(),
            'statut'       => 'EN_COURS',
            'user_id'      => $request->import_user_id,
            'tribunal_id'  => $request->import_tribunal_id,
        ]);

        DB::beginTransaction();

        try {
            $importer = new DossierImportEncours(
                $request->import_user_id,
                $request->import_tribunal_id,
                $importLog->id
            );
            Excel::import($importer, $request->file('file'));
            DB::commit();

            return response()->json([
                'message'             => 'تم استيراد الملفات الرائجة بنجاح !!!!',
                'nomdufichier'        => $importLog->nomdufichier,
                'nb_lignes_total'     => $importer->nbTotal,
                'nb_lignes_importees' => $importer->nbImportees,
                'nb_lignes_ignorees'  => $importer->nbTotal - $importer->nbImportees,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            $importLog->update([
                'statut'         => 'ECHEC',
                'message_erreur' => $e->getMessage(),
            ]);

            return response()->json([
                'error'   => 'هناك خطأ في الاستيراد: ' . $e->getMessage(),
                'details' => 'Ligne: ' . $e->getLine(),
            ], 500);
        }
    }

    public function historiqueEncours(Request $request)
    {
        $query = \App\Models\ImportEncoursLog::orderBy('date', 'desc');

        if ($request->tribunal_id) $query->where('tribunal_id', $request->tribunal_id);
        if ($request->user_id)     $query->where('user_id', $request->user_id);

        return response()->json($query->paginate(20), 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Méthodes CRUD générées par artisan (non utilisées)
    // ─────────────────────────────────────────────────────────────────────────
    public function index() {}
    public function store(Request $request) {}
    public function show(string $id) {}
    public function update(Request $request, string $id) {}
    public function destroy(string $id) {}
}
