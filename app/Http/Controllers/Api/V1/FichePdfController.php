<?php


namespace App\Http\Controllers\Api\V1;


//use \ArPHP\I18N\Arabic;

use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;


use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\Requette;
use Illuminate\Http\Request;


class FichePdfController extends Controller
{
    public function generatePdf($dossierId)
    {
        try {
            // 1. Récupération des données avec les relations
            $dossier = Dossier::with([
                'detenu',
                'detenu.profession',
                'detenu.nationalite',
                'garants',
                'userParquetObjet:id,name',
                'garants.province',
                'garants.tribunal',
                'comportement',
                'affaires',
                'requettes',
                'affaires.tribunal',
                'affaires.peine',
                'affaires.peine.prisons',
                'categoriedossier',
                'naturedossier',
                'typemotifdossier',
                'typedossier',
                'pjs',
                'pjs.requette',
                'pjs.affaire',
                'avis',
                'prison',
                'objetdemande',
                'sourcedemande',
                'LibelleTribunalUtilisateur'
            ])
                ->findOrFail($dossierId);

            // 2. Configuration mPDF pour l'arabe

            /* $mpdf = new Mpdf([
                'mode' => 'utf-8', // Supports Arabic text
                'format' => 'A4',
                'default_font' => 'Arial',
                'margin_top' => 5,
                'margin_bottom' => 5,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);
             $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'default_font' => 'changa', // Ou votre police configurée
                'autoArabic' => true,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ]);*/
            // Nettoyage des variables de config
            $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $mpdf = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    public_path('fonts'), // Chemin vers D:\xampp\htdocs\app_production\public\fonts
                ]),
                'default_font' => 'benaya-mohannad',
                'mode' => 'utf-8', // Supports Arabic text
                'format' => 'A4',
                'margin_top' => 5,
                'margin_bottom' => 5,
                'margin_left' => 15,
                'margin_right' => 15,
            ]);

            // 3. Chargement de la vue
            $html = view('pdf.dossier', compact('dossier'))->render();

            // 4. Écriture du contenu
            $mpdf->SetDirectionality('rtl');
            $mpdf->WriteHTML($html);

            // 5. Génération du PDF en mode "String" pour Laravel
            $pdfContent = $mpdf->Output('', 'S');

            $filename = 'dossier_' . $dossier->numero . '.pdf';

            // 6. Retour de la réponse avec les headers appropriés
            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                // Headers CORS si nécessaire (dépend de votre config globale)
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (MpdfException $e) {
            return response()->json(['error' => 'Erreur mPDF: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur Serveur: ' . $e->getMessage()], 500);
        }
    }

    public function generatePdfFromRequette($requetteId)
    {
        // On charge le dossier ET l'utilisateur de la requête elle-même
        $requette = Requette::with([
            'userParquetObjet', // IMPORTANT : pour éviter l'erreur sur le nom du député
            'dossier.detenu',
            'dossier.detenu.profession',
            'dossier.detenu.nationalite',
            'dossier.garants',
            'dossier.userParquetObjet',
            'dossier.affaires.tribunal',
            'dossier.typedossier',
            'dossier.avis',
            'dossier.LibelleTribunalUtilisateur'
        ])->findOrFail($requetteId);

        $dossier = $requette->dossier;

        if (!$dossier) {
            return response()->json(['error' => 'Dossier introuvable'], 404);
        }

        /*$mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'changa',
            'autoArabic' => true
        ]);

        $mpdf = new Mpdf([
            'mode' => 'utf-8', // Supports Arabic text
            'format' => 'A4',
            'default_font' => 'Arial',
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);*/

        // Nettoyage des variables de config


        $mpdf = new Mpdf([
            'default_font' => 'benaya-mohannad',
            'mode' => 'utf-8', // Supports Arabic text
            'format' => 'A4',
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_left' => 15,
            'margin_right' => 15,
        ]);


        // AJOUT : On passe 'dossier' ET 'requette' à la vue
        $html = view('pdf.dossier', compact('dossier', 'requette'))->render();

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('', 'S'))->header('Content-Type', 'application/pdf');
    }
}
