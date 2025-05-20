<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getDossierStats(Request $request)
    {
        $range = $request->input('range');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Dossier::query();

        if ($range === 'current_year') {
            $query->whereYear('created_at', now()->year);
        } elseif ($range === 'current_month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($range === 'current_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }

        return response()->json([
            'total' => $query->count(),
            /*'per_tribunal' => $query->select('tribunal_id')->with('tribunal:id,libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('tribunal_id')->get(),
               'per_tribunal' => $query->select('user_tribunal_libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('user_tribunal_libelle')->get(),
            'per_tribunal' => 100,*/
            'per_tribunal' => $query->whereNotNull('user_tribunal_libelle')
                ->select('user_tribunal_libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('user_tribunal_libelle')
                ->get(),

            'per_sex' => $query->select('user_tribunal_libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('user_tribunal_libelle')->get()


            /*$query->select('sex')
                ->selectRaw('count(*) as count')
                ->groupBy('sex')->get()*/,

            'per_typedossier' => $query->select('typedossier_id')->with('typedossier:id,libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('typedossier_id')->get(),

            'per_naturedossier' => $query->select('naturedossiers_id')->with('naturedossier:id,libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('naturedossiers_id')->get(),

            'traite_stats' => [
                'traite' => (clone $query)->where('etat', 'OK')->count(),
                'non_traite' => (clone $query)->where('etat', 'NT')->count(),
            ],
        ]);
    }
}
