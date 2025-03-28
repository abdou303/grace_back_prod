<?php


namespace App\Http\Controllers\Api\V1;


//use \ArPHP\I18N\Arabic;

use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class FichePdfController extends Controller
{
    public function generatePdf($dossierId)
    {
        try {
            $dossier = \App\Models\Dossier::with(['detenu', 'prison', 'affaires', 'requettes'])->findOrFail($dossierId);
            // Load the view as HTML
            $html = view('pdf.dossier', compact('dossier'))->render();




            $defaultConfig = (new ConfigVariables())->getDefaults();
            $defaultFontConfig = (new FontVariables())->getDefaults();

            $mpdf = new Mpdf([


                /*    'mode' => 'utf-8',

                'tempDir' => storage_path('temp'),
                'fontDir' => array_merge($defaultConfig['fontDir'], [
                    storage_path('fonts'),
                ]),
                'fontdata' => array_merge($defaultFontConfig['fontdata'], [
                    'changa' => [
                        'R' => "ae-almohanad.ttf",
                        'B' => "AL-Mohanad-Long-KAF.ttf",

                        'useKashida' => 75,

                    ]
                ]),
                'default_font' => 'changa', // Set as default Arabic font
*/

                'default_font' => 'kfgqpcuthmantahanaskh'



            ]);

            //$html = '<p style="font-family: almohanad; font-size: 20px;">تقرير</p>';




            // Write HTML and output PDF
            $mpdf->WriteHTML($html);
            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            }, 'example.pdf');
        } catch (MpdfException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
