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
    /*
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
    }*/
    public function headings(): array
    {
        return [
            'رقم الطلب',
            'الاسم الكامل',
            'رقم القضية',
            'رقم ن.ع',
            'تاريخ الطلب',
            'رقم الملف بالوزارة',
            'نوع الملف',
            'نوع الاجراء',
            'ممثل ن.ع',
        ];
    }

    public function map($item): array
    {
        $dossier = $item->dossier;
        $affaire = optional($dossier)->affaires?->first();

        return [
            $item->numero ?? '',
            trim(($dossier->detenu->nom ?? '') . ' ' . ($dossier->detenu->prenom ?? '')),
            $affaire ? "{$affaire->numero} /{$affaire->code} /{$affaire->annee}" : '',
            optional($dossier)->numeromp ?? '',
            $item->date ? \Carbon\Carbon::parse($item->date)->format('d/m/Y - H:i') : '',
            optional($dossier)->numero_dapg ?? '',
            optional($dossier?->typedossier)->libelle ?? '',
            optional($item->typerequette)->libelle ?? '',
            $this->libelleParquet($item, ''),
        ];
    }

    private function libelleParquet($item, string $defaultName): string
    {
        $user = $item->user_parquet_objet ?? $item->userParquetObjet ?? null;
        $name = $user->name ?? $defaultName;

        return match ($item->etat_parquet) {
            null, 'KO' => 'غير محال',
            'NT'       => "محال على ({$name}) - غير منجز",
            'TR'       => "محال على ({$name}) - منجز",
            default    => '',
        };
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $sheet->getStyle('A1:I1')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFD7B964'],
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                ]);

                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(20);
                }
            },
        ];
    }
}
