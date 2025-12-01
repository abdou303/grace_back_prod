<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Requette;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function getDossierStats(Request $request)
    {
        $range = $request->input('range');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Dossier::query();
        $query_requette = Requette::query();


        if ($range === 'current_year') {
            $query->whereYear('created_at', now()->year);
            $query_requette->whereYear('created_at', now()->year);
        } elseif ($range === 'current_month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $query_requette->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($range === 'current_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $query_requette->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
            $query_requette->whereBetween('created_at', [$from, $to]);
        }

        return response()->json([
            'total' => $query->count(),
            'total_tribunaux' => $query->WhereNotNull('numero')->count(),
            'total_requettes' => $query_requette->count(),
            'requettes_traites_stats' => [
                'confirme' => (clone $query_requette)->where('etat', 'TR')->count(),
                'non_confirme' => (clone $query_requette)->where('etat', 'NT')->count(),
                'traite' => (clone $query_requette)->where('etat_tribunal', 'TR')->count(),
                'non_traite' => (clone $query_requette)
                    ->where('etat', 'TR')
                    ->where(function ($query) {
                        $query->where('etat_tribunal', '!=', 'TR')
                            ->orWhereNull('etat_tribunal');
                    })
                    ->count(),
            ],
            /*'requettes_per_tribunal' => $query_requette->select('tribunal_id')->with('tribunal:id,libelle')
                ->where('etat', 'TR')
                ->selectRaw('count(*) as count')
                ->groupBy('tribunal_id')->get(),*/

            'requettes_per_tribunal' => $query_requette->select('tribunal_id')
                ->with('tribunal:id,libelle')
                ->where('etat', 'TR')
                ->selectRaw('
        count(*) as count,
        SUM(CASE WHEN etat_tribunal = \'TR\' THEN 1 ELSE 0 END) as count_ok,
        SUM(CASE WHEN etat_tribunal = \'NT\' OR etat_tribunal IS NULL THEN 1 ELSE 0 END) as count_ko_or_null
    ')
                ->groupBy('tribunal_id')
                ->get(),

            /*
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


    public function getDossierStatsByTR(Request $request, $tr_id)
    {
        $range = $request->input('range');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Dossier::query();
        $query_requette = Requette::query();
        $query_requette->where('tribunal_id', $tr_id);



        if ($range === 'current_year') {
            $query->whereYear('created_at', now()->year);
            $query_requette->whereYear('created_at', now()->year);
        } elseif ($range === 'current_month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $query_requette->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($range === 'current_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $query_requette->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
            $query_requette->whereBetween('created_at', [$from, $to]);
        }

        return response()->json([
            'total' => $query->count(),
            'total_requettes' => $query_requette->where('etat', 'TR')->count(),
            'requettes_traites_stats' => [
                'confirme' => (clone $query_requette)->where('etat', 'TR')->count(),
                'non_confirme' => (clone $query_requette)->where('etat', 'NT')->count(),
                'traite' => (clone $query_requette)->where('etat_tribunal', 'TR')->count(),
                'non_traite' => (clone $query_requette)
                    ->where('etat', 'TR')
                    ->where(function ($query) {
                        $query->where('etat_tribunal', '!=', 'TR')
                            ->orWhereNull('etat_tribunal');
                    })
                    ->count(),
            ],

            'per_tribunal' => $query->whereNotNull('user_tribunal_libelle')
                ->select('user_tribunal_libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('user_tribunal_libelle')
                ->get(),

            'per_sex' => $query->select('user_tribunal_libelle')
                ->selectRaw('count(*) as count')
                ->groupBy('user_tribunal_libelle')->get(),



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
