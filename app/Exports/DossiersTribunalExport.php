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
 * Reproduit l'export ExcelJS de list-dossiers-tribunal.component.ts
 * (5 colonnes : الرقم, نوع الملف, تاريخ التسجيل, الاسم الكامل, ب ت و).
 */
class DossiersTribunalExport implements FromCollection, WithHeadings, WithMapping, WithEvents
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
        return ['الرقم', 'نوع الملف', 'تاريخ التسجيل', 'الاسم الكامل', 'ب ت و'];
    }

    public function map($item): array
    {
        return [
            $item->numero ?? '',
            optional($item->typedossier)->libelle ?? '',
            $item->created_at ? $item->created_at->format('Y-m-d H:i') : '',
            trim(($item->detenu->nom ?? '') . ' ' . ($item->detenu->prenom ?? '')),
            optional($item->detenu)->cin ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $sheet->getStyle('A1:E1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF325D88'],
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                foreach (range('A', 'E') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(22);
                }
            },
        ];
    }
}
