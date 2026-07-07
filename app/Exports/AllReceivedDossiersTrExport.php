<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Reproduit l'export ExcelJS de all-received-dossiers-from-tr.component.ts
 * (colonnes cin / numeromp en plus par rapport à DossiersTrExport).
 */
class AllReceivedDossiersTrExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected Collection $dossiers;

    public function __construct($dossiers)
    {
        $this->dossiers = collect($dossiers);
    }

    public function collection()
    {
        return $this->dossiers;
    }

    public function headings(): array
    {
        return [
            'الرقم',
            'الرقم بالوزارة',
            'رقم ب ت و',
            'رقم النيابة',
            'الوضعية',
            'المصدر',
            'رقم القضية',
            'تاريخ التسجيل',
            'المتهم',
            'نوع الملف',
        ];
    }

    public function map($item): array
    {
        $etat = $item->etat;
        if ($item->etat === 'NT') {
            $etat = 'طلب جديد';
        } elseif ($item->etat === 'OK' && $item->tr_tribunal !== 'OK') {
            $etat = 'في طور التجهيز';
        } elseif ($item->etat === 'OK' && $item->tr_tribunal === 'OK') {
            $etat = 'ملف جاهز';
        }

        $numerosAffaire = $item->affaires
            ->pluck('numeroaffaire')
            ->filter()
            ->implode(' : ');

        return [
            $item->numero ?? '',
            $item->numero_dapg ?? '',
            optional($item->detenu)->cin ?? '',
            $item->numeromp ?? '',
            $etat ?? '',
            $item->user_tribunal_libelle
                ?? optional($item->libelleTribunalUtilisateur)->libelle
                ?? '',
            $numerosAffaire,
            $item->created_at ? $item->created_at->format('Y-m-d H:i') : '',
            trim(($item->detenu->nom ?? '') . ' ' . ($item->detenu->prenom ?? '')),
            optional($item->typedossier)->libelle ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $lastColumn = 'J';

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD7B964'],
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                foreach (range('A', $lastColumn) as $col) {
                    $sheet->getColumnDimension($col)->setWidth(22);
                }
            },
        ];
    }
}
