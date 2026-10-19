<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ChoixNonRecours;
use App\Models\Dossier;
use App\Models\Requette;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

/**
 * PDF "الشهادة الضبطية" — un document par affaire (has_choix_non_recours = 1).
 * Traitement séparé du ملتمس النيابة العامة (FichePdfController / pdf.dossier) :
 * contrôleur, route et vue Blade dédiés. Le PDF est généré et affiché, sans OpenBee.
 *
 * GET /dossiers/{dossier_id}/affaires/{affaire_id}/pdf-non-recours[?requette_id=X]
 */
class NonRecoursPdfController extends Controller
{
    public function generate(Request $request, $dossierId, $affaireId)
    {
        try {
            // 1. Dossier + relations utilisées par la vue
            $dossier = Dossier::with([
                'detenu',
                'typedossier',
                'avis',
                // 'userParquetObjet:id,name',
                'LibelleTribunalUtilisateur',
            ])->findOrFail($dossierId);

            // 2. L'affaire doit appartenir à ce dossier
            $affaire = $dossier->affaires()
                ->with('tribunal')
                ->where('affaires.id', $affaireId)
                ->firstOrFail();

            if (!$affaire->has_choix_non_recours) {
                return response()->json([
                    'error' => 'لم يتم إدخال الاختيارات الخاصة بالشهادة الضبطية لهذه القضية',
                ], 422);
            }

            // 3. Libellé du choix (الاختيار)
            $choix = $affaire->choix_non_recours_id
                ? ChoixNonRecours::find($affaire->choix_non_recours_id)
                : null;

            // 3-bis. Utilisateur du greffe qui a choisi "نعم" (zone إمضاء)
            $userChoix = $affaire->user_choix_non_recours_id
                ? User::find($affaire->user_choix_non_recours_id)
                : null;

            // 4. Contexte requête (optionnel) : date + nom du substitut, comme le ملتمس
            $requette = null;
            if ($request->filled('requette_id')) {
                $requette = Requette::with('userParquetObjet')
                    ->where('dossier_id', $dossier->id)
                    ->findOrFail($request->requette_id);
            }

            // 5. mPDF (même configuration que le ملتمس)
            /*$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();

            $mpdf = new Mpdf([
                'fontDir' => array_merge($defaultConfig['fontDir'], [public_path('fonts')]),
                'default_font' => 'benaya-mohannad',*/

            // 5. mPDF (même configuration que le ملتمس)
            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

            $mpdf = new Mpdf([
                // 'benaya-mohannad' (CSS) => police arabe intégrée à mPDF (XB Riyaz, OTL activé)
                'fonttrans' => $defaultFontConfig['fonttrans'] + ['benaya-mohannad' => 'xbriyaz'],
                'default_font' => 'xbriyaz',
                'mode' => 'utf-8',
                'format' => 'A4',
                //'format' => 'A5',
                'margin_top' => 5,
                'margin_bottom' => 5,
                'margin_left' => 15,
                'margin_right' => 15,
                'autoArabic' => true,
                'autoScriptToLang' => true,   // marque le texte arabe => liaison correcte des lettres
                'autoLangToFont' => false,    // garde xbriyaz, pas la police choisie par mPDF
            ]);

            $html = view('pdf.non_recours', compact('dossier', 'affaire', 'choix', 'requette', 'userChoix'))->render();

            $mpdf->SetDirectionality('rtl');
            $mpdf->WriteHTML($html);

            $filename = 'shahada_dabtiya_' . $dossier->id . '_' . $affaire->id . '.pdf';

            return response($mpdf->Output('', 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'الملف أو القضية غير موجود'], 404);
        } catch (MpdfException $e) {
            return response()->json(['error' => 'Erreur mPDF: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur Serveur: ' . $e->getMessage()], 500);
        }
    }
}
