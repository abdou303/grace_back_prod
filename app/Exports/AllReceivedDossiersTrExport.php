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
            'التهمة',
            'القضية الأولى',
            'التهمة (ق 1)',
            'تاريخ الحكم(ق 1)',
            'تاريخ الحكم',
            'المنطوق(ق 1)',
            'المنطوق',
            'نوع الملف',
            'تاريخ الخروج',
            'تاريخ الانجاز',
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

        // الرقم : R-XXXXXXXXX si le dossier vient d'une requête, sinon D-XXXXXXXXX
        $numero = $item->numero ?? '';
        if ($item->originedossier === 'R') {
            $requetteCat1 = $item->requettes
                ->filter(fn($r) => optional($r->typerequette)->cat === 'CAT-1')
                ->sortByDesc('id')
                ->first();
            $numero = $requetteCat1->numero ?? $numero;
        }

        $tuhma = $item->affaires
            ->pluck('conenujugement') // ⚠️ التهمة : remplacer par la bonne colonne si besoin
            ->filter()
            ->implode(' : ');

        // القضية الأولى
        $premiereAffaire = $item->affaires->sortBy('id')->first();
        $affairePremiere = $premiereAffaire->numeroaffaire ?? '';

        // التهمة الأولى : التهمة de la première affaire (même logique que القضية الأولى)
        $tuhmaPremiere = $premiereAffaire->conenujugement ?? '';

        // تاريخ الحكم(ق 1) / تاريخ الحكم : datejujement (première affaire / toutes les affaires)
        $dateJugementPremiere = $this->formatDate($premiereAffaire->datejujement ?? null, 'Y-m-d');
        $datesJugement = $item->affaires
            ->map(fn($a) => $this->formatDate($a->datejujement, 'Y-m-d'))
            ->filter()
            ->implode(' : ');

        // المنطوق(ق 1) / المنطوق : conenujugement (première affaire / toutes les affaires)
        $mantoukPremiere = $premiereAffaire->conenujugement ?? '';
        $mantouk = $item->affaires
            ->pluck('conenujugement')
            ->filter()
            ->implode(' : ');

        // تاريخ الانجاز : même logique que la colonne date_readiness de la grille
        $dateInjaz = null;
        if ($item->originedossier === 'D') {
            $dateInjaz = $item->date_etat_ok;
        } elseif ($item->originedossier === 'R' && $item->tr_tribunal === 'OK') {
            $dateInjaz = $item->date_tr_tribunal;
        }

        return [
            $numero,
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
            $tuhma,
            $affairePremiere,
            $tuhmaPremiere,
            $dateJugementPremiere,
            $datesJugement,
            $mantoukPremiere,
            $mantouk,
            optional($item->typedossier)->libelle ?? '',
            $this->formatDate($item->date_sortie, 'Y-m-d'),
            $this->formatDate($dateInjaz, 'Y-m-d - H:i'),
        ];
    }

    private function formatDate($value, string $format): string
    {
        if (empty($value)) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $lastColumn = 'S';

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
