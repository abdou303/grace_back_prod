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
            // Define font directories
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = array_merge($defaultConfig['fontDir'], [
                storage_path('fonts/')
            ]);

            // Define font data
            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = array_merge($defaultFontConfig['fontdata'], [
                'changa' => [
                    'R' => 'almohannadbold.ttf', // Ensure filename matches exactly
                ],
            ]);

            // Create mPDF instance
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font' => 'changa', // Use the registered font
                'fontDir' => $fontDirs,
                'fontdata' => $fontData,
            ]);

            // Enable RTL and Arabic shaping
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;

            $dossier = \App\Models\Dossier::with(['detenu', 'prison', 'affaires', 'requettes'])->findOrFail($dossierId);

            // Load the view as HTML
            $html = view('pdf.dossier', compact('dossier'))->render();

            // Arabic content
            /* $html = '
            <style>
                body { font-family: "changa"; font-size: 16px; direction: rtl; text-align: right; }
                h1 { font-family: "changa"; font-weight: bold; }
            </style>
            <h1>مرحبا بالعالم</h1>
            <p>هذا هو نص تجريبي باللغة العربية</p>
        ';*/

            // Write to PDF
            $mpdf->WriteHTML($html);

            return response()->streamDownload(function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            }, 'example.pdf');
        } catch (MpdfException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
