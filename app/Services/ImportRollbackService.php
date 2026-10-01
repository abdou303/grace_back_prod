<?php

namespace App\Services;

use App\Models\Affaire;
use App\Models\Detenu;
use App\Models\Dossier;
use App\Models\ImportEncoursLog;
use App\Models\ImportLog;
use App\Models\ImportLogItem;
use App\Models\Requette;
use Illuminate\Support\Facades\DB;

class ImportRollbackService
{
    /**
     * Annule un import (CLASSIQUE ou ENCOURS) en supprimant
     * toutes les lignes créées et tracées dans import_log_items.
     */
    public function rollback(string $type, int $logId): array
    {
        $logModel = $type === 'CLASSIQUE' ? ImportLog::class : ImportEncoursLog::class;
        $log = $logModel::findOrFail($logId);

        if ($log->statut === 'ANNULE') {
            throw new \RuntimeException('تم إلغاء هذا الاستيراد مسبقا.');
        }

        if (!ImportLogItem::where('import_type', $type)->where('import_log_id', $logId)->exists()) {
            throw new \RuntimeException('لا توجد بيانات قابلة للإلغاء لهذا الاستيراد.');
        }

        // Sous-requête des IDs tracés (évite la limite de 2100 paramètres de SQL Server)
        $ids = fn(string $model) => ImportLogItem::select('model_id')
            ->where('import_type', $type)
            ->where('import_log_id', $logId)
            ->where('model', $model);

        // Garde-fou 1 : requêtes importées déjà traitées
        $traitees = Requette::whereIn('id', $ids('Requette'))
            ->where(fn($q) => $q->where('etat', '!=', 'NT')->orWhere('etat_tribunal', '!=', 'NT'))
            ->exists();

        if ($traitees) {
            throw new \RuntimeException('لا يمكن إلغاء الاستيراد: بعض الطلبات تمت معالجتها.');
        }

        // Garde-fou 2 : requêtes ajoutées après l'import sur les dossiers importés
        $ajoutees = Requette::whereIn('dossier_id', $ids('Dossier'))
            ->whereNotIn('id', $ids('Requette'))
            ->exists();

        if ($ajoutees) {
            throw new \RuntimeException('لا يمكن إلغاء الاستيراد: تمت إضافة طلبات جديدة على الملفات المستوردة.');
        }

        $pivot = (new Dossier)->affaires();

        return DB::transaction(function () use ($ids, $pivot, $type, $logId, $log) {
            $stats = [
                'requettes' => Requette::whereIn('id', $ids('Requette'))->count(),
                'dossiers'  => Dossier::whereIn('id', $ids('Dossier'))->count(),
            ];

            // Dossiers existants (non créés par l'import) sur lesquels l'import avait ajouté une requête
            // → ils ne seront pas supprimés, on pourra y tracer l'annulation
            // $dossiersConserves = Requette::whereIn('id', $ids('Requette'))
            //     ->whereNotIn('dossier_id', $ids('Dossier'))
            //     ->pluck('dossier_id')
            //     ->unique();

            // 1. Historique des opérations liées aux requêtes importées
            DB::table('historiques_operations')
                ->whereIn('requette_id', $ids('Requette'))
                ->delete();

            // 2. Requêtes
            Requette::whereIn('id', $ids('Requette'))->delete();

            // 3. Liaison dossier <-> affaires
            DB::table($pivot->getTable())
                ->whereIn($pivot->getForeignPivotKeyName(), $ids('Dossier'))
                ->delete();

            // 4. Affaires, dossiers, détenus
            Affaire::whereIn('id', $ids('Affaire'))->delete();
            Dossier::whereIn('id', $ids('Dossier'))->delete();
            Detenu::whereIn('id', $ids('Detenu'))->delete();

            // 5. Traçage de l'annulation dans historiques_operations (à activer plus tard)
            // $operationService = new \App\Services\OperationService();
            // foreach ($dossiersConserves as $dossierId) {
            //     $operationService->logOperation(
            //         $dossierId,
            //         'DAPG-DELETE-IMPORT',
            //         null,
            //         auth()->id()
            //     );
            // }

            // 6. Nettoyage du traçage + statut
            ImportLogItem::where('import_type', $type)->where('import_log_id', $logId)->delete();
            $log->update(['statut' => 'ANNULE']);

            return $stats;
        });
    }
}
