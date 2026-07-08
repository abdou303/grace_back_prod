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
 * Reproduit l'export ExcelJS partagé par list-add-demande-copie-to-requettes
 * et list-all-bo-demandes (mêmes 6 colonnes, même couleur d'en-tête #D7B964).
 */
class BoDemandesExport implements FromCollection, WithHeadings, WithMapping, WithEvents
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
            'رقم الملف',
            'الاسم الكامل',
            'رقم القضية',
            'تاريخ الاستيراد',
            'المحكمة',
            'نوع الاجراء',
        ];
    }

    public function map($item): array
    {
        $affaire = $item->dossier?->affaires?->first();

        return [
            $item->dossier?->numero_dapg ?? '',
            trim(($item->dossier?->detenu?->nom ?? '') . ' ' . ($item->dossier?->detenu?->prenom ?? '')),
            $affaire ? "{$affaire->numero} /{$affaire->code} /{$affaire->annee}" : '',
            $item->date ?? '',
            $item->tribunal?->libelle ?? '',
            $item->typerequette?->libelle ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $sheet->getStyle('A1:F1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD7B964'],
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                $widths = [20, 35, 25, 20, 45, 30];
                foreach (range('A', 'F') as $i => $col) {
                    $sheet->getColumnDimension($col)->setWidth($widths[$i]);
                }
            },
        ];
    }
}
