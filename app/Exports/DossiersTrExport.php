<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Reproduit le fichier généré auparavant côté Angular avec ExcelJS
 * (mêmes colonnes, même couleur d'en-tête #D7B964, RTL).
 *
 * Nécessite : composer require maatwebsite/excel
 */
class DossiersTrExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents
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
        /*$etat = $item->etat;
        if ($item->etat === 'NT') {
            $etat = 'طلب جديد';
        } elseif ($item->etat === 'OK' && $item->tr_tribunal !== 'OK') {
            $etat = 'في طور التجهيز';
        } elseif ($item->etat === 'OK' && $item->tr_tribunal === 'OK') {
            $etat = 'ملف جاهز';
        }*/
        $etat = $this->libelleEtat($item);
        $affaire = $item->affaires->first();

        // الرقم : R-XXXXXXXX (requête CAT-1) si origine R, sinon numero du dossier ; jamais vide
        $requetteCat1 = $item->requettes
            ->filter(fn($r) => optional($r->typerequette)->cat === 'CAT-1' && !empty($r->numero))
            ->sortByDesc('id')
            ->first();
        $numeroR = $requetteCat1->numero ?? null;

        $numero = ($item->originedossier === 'R' && $numeroR)
            ? $numeroR
            : ($item->numero ?: ($numeroR ?? ''));
        return [
            //$item->numero ?? '',
            $numero,
            $item->numero_dapg ?? '',
            $etat ?? '',
            $item->user_tribunal_libelle
                ?? optional($item->libelleTribunalUtilisateur)->libelle
                ?? '',
            optional($affaire)->numeroaffaire ?? '',
            $item->created_at ? $item->created_at->format('Y-m-d H:i') : '',
            trim(($item->detenu->nom ?? '') . ' ' . ($item->detenu->prenom ?? '')),
            optional($item->typedossier)->libelle ?? '',
        ];
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFD7B964']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']]],
        ];
    }
    /**
     * Règle d'affichage de "الوضعية" (identique à la grille et au filtre backend)
     */
    private function libelleEtat($item): string
    {
        if ($item->etat === 'OK' && $item->tr_tribunal === 'OK') {
            return 'ملف جاهز';
        }

        $enCours = $item->etat === 'OK';

        if ($item->originedossier === 'D') {
            $enCours = $enCours
                || ($item->pjs_count ?? 0) >= 3
                || $item->etat_greffe === 'NT'
                || $item->etat_parquet === 'NT'
                || $item->etat_greffe === 'TR'
                || $item->etat_parquet === 'TR';
        } elseif ($item->originedossier === 'R') {
            $enCours = $enCours || $item->requettes->contains(function ($r) {
                return optional($r->typerequette)->cat === 'CAT-1'
                    && (($r->pjs_count ?? 0) >= 3
                        || $r->etat_greffe === 'NT'
                        || $r->etat_parquet === 'NT' || $r->etat_greffe === 'TR'
                        || $r->etat_parquet === 'TR');
            });
        }

        if ($enCours) {
            return 'في طور التجهيز';
        }
        if ($item->etat === 'NT') {
            return 'طلب جديد';
        }

        return (string) ($item->etat ?? '');
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $headerRow = 1;
                $lastColumn = 'H';

                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
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
