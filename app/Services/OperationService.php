<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OperationService
{
    // Cache pour éviter de répéter les requêtes SQL sur la table 'operations'
    protected static $operationCache = [];

    /**
     * Enregistre une trace dans l'historique en utilisant le CODE de l'opération
     */
    public function logOperation(int $dossierId, string $operationCode, ?int $reqId = null, int $userId)
    {
        // 1. Récupérer l'ID de l'opération via son code (avec mise en cache)
        if (!isset(self::$operationCache[$operationCode])) {
            self::$operationCache[$operationCode] = DB::table('operations')
                ->where('code', $operationCode)
                ->value('id');
        }

        $opId = self::$operationCache[$operationCode];

        // 2. Si le code n'existe pas en base, on peut soit lever une erreur, soit ignorer
        if (!$opId) {
            throw new \Exception("Le code opération '$operationCode' n'existe pas dans la table operations.");
        }

        // 3. Insertion avec votre logique initiale
        return DB::table('historiques_operations')->insert([
            'dossier_id'   => $dossierId,
            'requette_id'  => $reqId,
            'user_id'      => $userId,
            'operation_id' => $opId,
            'date_action'  => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }
}
