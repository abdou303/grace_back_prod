<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ImportRollbackService;

class ImportRollbackController extends Controller
{
    public function __construct(private ImportRollbackService $service) {}

    public function rollbackImport($id)
    {
        return $this->doRollback('CLASSIQUE', (int) $id);
    }

    public function rollbackImportEncours($id)
    {
        return $this->doRollback('ENCOURS', (int) $id);
    }

    private function doRollback(string $type, int $id)
    {
        try {
            $stats = $this->service->rollback($type, $id);
            return response()->json(['message' => 'تم إلغاء الاستيراد بنجاح.'] + $stats);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
