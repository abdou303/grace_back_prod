<?php
/*
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();

return [
    'fontDir' => array_merge($defaultConfig['fontDir'], [
        storage_path('fonts'), // Custom fonts directory
    ]),
    'fontdata' => array_merge($defaultFontConfig['fontdata'], [
        'almohanad' => [
            'R'  => 'ae-almohanad.ttf',    // Regular
            'B'  => 'AL-Mohanad-Long-KAF.ttf', // Bold
            'useOTL' => 0xFF, // Enable advanced OpenType layout
            'useKashida' => 75, // Enable Kashida justification
        ],
    ]),
    'default_font' => 'almohanad'
];


// 1. Définir les variables de config par défaut de mPDF
$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

// 2. Créer l'instance avec TA configuration
$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        storage_path('fonts'), // Ton dossier contenant les .ttf
    ]),
    'fontdata' => $fontData + [
        'almohanad' => [ // C'est le nom que tu utiliseras en CSS
            'R' => 'ae-almohanad.ttf', // Le nom exact du fichier pour le texte normal
            'B' => 'AL-Mohanad-Long-KAF.ttf', // Le fichier pour le gras (ici le même)
            'useOTL' => 0xFF,     // Important pour l'arabe
            'useKashida' => 75,
        ]
    ],
    'default_font' => 'almohanad', // Police par défaut
    'mode' => 'utf-8',
    'format' => 'A4',
    'autoArabic' => true,
    'autoScriptToLang' => true,
    'autoLangToFont' => true,
]);
*/