<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Builder;

trait HasServerSideRowModel
{
    protected function serverSideRows(
        Request $request,
        Builder $query,
        array $allowedSortColumns,
        array $defaultSort = ['id', 'desc'],
    ): JsonResponse {
        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $perPage  = max(1, $endRow - $startRow);
        $page     = (int) floor($startRow / $perPage) + 1;

        $this->applyServerSideSorting($query, $request->input('sortModel', []), $allowedSortColumns, $defaultSort);

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'rows'    => $paginated->items(),
            'lastRow' => $paginated->total(),
        ]);
    }

    protected function serverSideRowsWithResource(
        Request $request,
        Builder $query,
        string $resourceClass,
        array $allowedSortColumns,
        array $defaultSort = ['id', 'desc'],
    ): JsonResponse {
        $startRow = (int) $request->input('startRow', 0);
        $endRow   = (int) $request->input('endRow', 20);
        $perPage  = max(1, $endRow - $startRow);
        $page     = (int) floor($startRow / $perPage) + 1;

        $this->applyServerSideSorting($query, $request->input('sortModel', []), $allowedSortColumns, $defaultSort);

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'rows'    => $resourceClass::collection($paginated->items()),
            'lastRow' => $paginated->total(),
        ]);
    }

    private function applyServerSideSorting(Builder $query, array $sortModel, array $allowedColumns, array $defaultSort): void
    {
        if (empty($sortModel)) {
            $query->orderBy($defaultSort[0], $defaultSort[1] ?? 'desc');
            return;
        }

        foreach ($sortModel as $sort) {
            $colId     = $sort['colId'] ?? null;
            $direction = ($sort['sort'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

            if (in_array($colId, $allowedColumns, true)) {
                $query->orderBy($colId, $direction);
            }
        }
    }
}
