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
        /* $etat = $item->etat;
        if ($item->etat === 'NT') {
            $etat = 'طلب جديد';
        } elseif ($item->etat === 'OK' && $item->tr_tribunal !== 'OK') {
            $etat = 'في طور التجهيز';
        } elseif ($item->etat === 'OK' && $item->tr_tribunal === 'OK') {
            $etat = 'ملف جاهز';
        }*/
        $etat = $this->libelleEtat($item);
        $numerosAffaire = $item->affaires
            ->pluck('numeroaffaire')
            ->filter()
            ->implode(' : ');

        /* // الرقم : R-XXXXXXXXX si le dossier vient d'une requête, sinon D-XXXXXXXXX
        $numero = $item->numero ?? '';
        if ($item->originedossier === 'R') {
            $requetteCat1 = $item->requettes
                ->filter(fn($r) => optional($r->typerequette)->cat === 'CAT-1')
                ->sortByDesc('id')
                ->first();
            $numero = $requetteCat1->numero ?? $numero;
        }*/
        // الرقم : R-XXXXXXXX (requête CAT-1) si origine R, sinon numero du dossier ; jamais vide
        $requetteCat1 = $item->requettes
            ->filter(fn($r) => optional($r->typerequette)->cat === 'CAT-1' && !empty($r->numero))
            ->sortByDesc('id')
            ->first();
        $numeroR = $requetteCat1->numero ?? null;

        $numero = ($item->originedossier === 'R' && $numeroR)
            ? $numeroR
            : ($item->numero ?: ($numeroR ?? ''));
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
