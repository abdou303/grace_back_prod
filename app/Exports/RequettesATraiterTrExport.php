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
 * Reproduit l'export ExcelJS de list-requettes-a-traiter-tr.component.ts
 * (mêmes 7 colonnes, même couleur d'en-tête #D7B964).
 */
class RequettesATraiterTrExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected Collection $requettes;

    public function __construct($requettes)
    {
        $this->requettes = collect($requettes);
    }

    public function collection()
    {
        return $this->requettes;
    }

    public function headings(): array
    {
        return [
            'رقم الطلب',
            'تاريخ الطلب',
            'رقم الملف بالوزارة',
            'الاسم الكامل',
            'رقم القضية',
            'نوع الملف',
            'نوع الاجراء',
        ];
    }

    public function map($item): array
    {
        $affaire = optional($item->dossier)->affaires?->first();

        return [
            $item->numero ?? '',
            $item->date ?? '',
            optional($item->dossier)->numero_dapg ?? '',
            trim(($item->dossier->detenu->nom ?? '') . ' ' . ($item->dossier->detenu->prenom ?? '')),
            $affaire ? "{$affaire->numero}/{$affaire->code}/{$affaire->annee}" : '',
            optional($item->dossier?->typedossier)->libelle ?? '',
            optional($item->typerequette)->libelle ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $sheet->getStyle('A1:G1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD7B964'],
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                foreach (range('A', 'G') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(20);
                }
            },
        ];
    }
}
