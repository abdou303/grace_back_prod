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

            // Configure mPDF
            $mpdf = new Mpdf([
                'default_font' => 'kfgqpcuthmantahanaskh',
            ]);

            // Write HTML into PDF
            $mpdf->WriteHTML($html);

            // Set filename dynamically
            $filename = $dossier->numero . '.pdf';

            // Open PDF in new tab
            return response($mpdf->Output($filename, 'I'))
                ->header('Content-Type', 'application/pdf')
                ->header('Access-Control-Allow-Origin', 'http://localhost:4200')
                ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        } catch (MpdfException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
