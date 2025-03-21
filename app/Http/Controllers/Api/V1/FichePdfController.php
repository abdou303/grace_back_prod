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
    public function generatePdf()
    {
        try {
            // Define custom font directory
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = array_merge($defaultConfig['fontDir'], [
                storage_path('fonts/')
            ]);

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = array_merge($defaultFontConfig['fontdata'], [
                'amiri' => [  // Define the font family name
                    'R' => 'Changa-Regular.ttf',

                ],
            ]);

            // Create a new Mpdf instance with the custom font
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'amiri', // Apply the custom Arabic font
                'fontDir' => $fontDirs,
                'fontdata' => $fontData,
            ]);

            // Set text direction to RTL
            //$mpdf->SetDirectionality('rtl');

            // Example HTML content
            $html = '
                <style>
                    body { font-family: "amiri"; font-size: 16px; }
                    h1 { font-family: "amiri"; text-align: center; font-weight: bold; }
                </style>
                <h1>مرحبا بالعالم</h1>
                <p>هذا هو نص تجريبي باللغة العربية</p>
            ';

            // Write HTML to PDF
            $mpdf->WriteHTML($html);

            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            }, 'example.pdf');
        } catch (MpdfException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
