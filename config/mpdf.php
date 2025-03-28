<?php

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
