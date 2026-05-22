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
        $query_dossier = Dossier::query()->where(function ($query) {
            $query->where('has_antecedent', '!=', 'OUI')
                ->orWhereNull('has_antecedent');
        });
        $query_requette = Requette::query();



        if ($range === 'current_year') {
            $query->whereYear('created_at', now()->year);
            $query_requette->whereYear('created_at', now()->year);
            $query_dossier->whereYear('created_at', now()->year);
        } elseif ($range === 'current_month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $query_dossier->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $query_requette->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($range === 'current_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $query_dossier->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);

            $query_requette->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
            $query_dossier->whereBetween('created_at', [$from, $to]);
            $query_requette->whereBetween('created_at', [$from, $to]);
        }
        // --- DÉBUT DE LA MODIFICATION ---
        $typeDossier = $request->input('typedossier_filter');
        if ($typeDossier && in_array($typeDossier, [1, 2])) {
            $query->where('typedossier_id', $typeDossier);
            $query_dossier->where('typedossier_id', $typeDossier);
            $query_requette->whereHas('dossier', function ($q) use ($typeDossier) {
                $q->where('typedossier_id', $typeDossier);
            });
        }
        // --- FIN DE LA MODIFICATION ---
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
            /**************************************************** */
            'total_dossiers' => $query_dossier->where('originedossier', '=', 'D')->count(),
            'dossiers_traites_stats' => [
                'non_traites' => (clone $query_dossier)->where('etat', '!=', 'OK')->count(),
                'traites' => (clone $query_dossier)->where('etat', 'OK')->count(),
                'recus_dapg' => (clone $query_dossier)->where('tr_dapg', 'OK')->count(),

            ],
            'dossiers_per_tribunal' => $query_dossier->select('user_tribunal_id')
                ->with('LibelleTribunalUtilisateur:id,libelle')
                ->where('originedossier', 'D')
                ->where(function ($query) {
                    $query->where('has_antecedent', '!=', 'OUI')
                        ->orWhereNull('has_antecedent');
                })
                ->selectRaw('
        count(*) as count,
        SUM(CASE WHEN etat = \'OK\' THEN 1 ELSE 0 END) as count_ok,
        SUM(CASE WHEN etat != \'OK\'  THEN 1 ELSE 0 END) as count_ko_or_null,
        SUM(CASE WHEN tr_dapg = \'OK\'  THEN 1 ELSE 0 END) as count_recus_dapg

    ')
                ->groupBy('user_tribunal_id')
                ->get(),
            /****************************************** */
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
        $query_dossier = Dossier::query();
        $query_dossier->where('user_tribunal_id', $tr_id)->where(function ($query) {
            $query->where('has_antecedent', '!=', 'OUI')
                ->orWhereNull('has_antecedent');
        });
        $query_requette = Requette::query();
        $query_requette->where('tribunal_id', $tr_id);



        if ($range === 'current_year') {
            $query->whereYear('created_at', now()->year);
            $query_requette->whereYear('created_at', now()->year);
            $query_dossier->whereYear('created_at', now()->year);
        } elseif ($range === 'current_month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $query_requette->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            $query_dossier->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        } elseif ($range === 'current_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $query_requette->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $query_dossier->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
            $query_requette->whereBetween('created_at', [$from, $to]);
            $query_dossier->whereBetween('created_at', [$from, $to]);
        }

        // --- DÉBUT DE LA MODIFICATION ---
        $typeDossier = $request->input('typedossier_filter');
        if ($typeDossier && in_array($typeDossier, [1, 2])) {
            $query->where('typedossier_id', $typeDossier);
            $query_dossier->where('typedossier_id', $typeDossier);
            $query_requette->whereHas('dossier', function ($q) use ($typeDossier) {
                $q->where('typedossier_id', $typeDossier);
            });
        }
        // --- FIN DE LA MODIFICATION ---

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
            'total_dossiers' => $query_dossier->where('originedossier', '=', 'D')->count(),
            'dossiers_traites_stats' => [
                'non_traites' => (clone $query_dossier)->where('etat', '!=', 'OK')->count(),
                'traites' => (clone $query_dossier)->where('etat', 'OK')->count(),
                'recus_dapg' => (clone $query_dossier)->where('tr_dapg', 'OK')->count(),

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

    public function getOwnDossierStatsByTR(Request $request, $tr_id)
    {
        $range = $request->input('range');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = Dossier::query();



        $query_requette = Requette::query();
        $query_requette->where('tribunal_id', $tr_id);



        if ($range === 'current_year') {
            $query->whereYear('created_at', now()->year);
            // $query_requette->whereYear('created_at', now()->year);
        } elseif ($range === 'current_month') {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
            //$query_requette->whereMonth('created_at', now()->month)
            //->whereYear('created_at', now()->year);
        } elseif ($range === 'current_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            //$query_requette->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
            //  $query_requette->whereBetween('created_at', [$from, $to]);
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
