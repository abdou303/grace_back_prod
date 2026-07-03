<?php

namespace App\Imports;

use App\Models\Dossier;
use App\Models\Detenu;
use App\Models\Affaire;
use App\Models\ImportEncoursLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class DossierImportEncours implements ToCollection, WithHeadingRow
{
    private $userId;
    private $tribunalId;
    private $importLogId;

    public int $nbTotal     = 0;
    public int $nbImportees = 0;

    public function __construct($userId, $tribunalId, $importLogId = null)
    {
        $this->userId      = $userId;
        $this->tribunalId  = $tribunalId;
        $this->importLogId = $importLogId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ignorer les lignes vides
            if (empty($row['numero_dossier']) && empty($row['nom'])) {
                $this->nbTotal++;
                continue;
            }

            $this->nbTotal++;

            DB::transaction(function () use ($row) {
                // Vérifier si le dossier existe déjà (même numéro + même type)
                $existingDossier = Dossier::where('numero_dapg', $row['numero_dossier'])
                    ->where('typedossier_id', $row['typedossier_id'])
                    ->first();

                if ($existingDossier) {
                    // Dossier déjà présent → on ignore (pas de requête à créer ici)
                    // On ne compte pas comme importé
                    return;
                }

                // 1. Création du Détenu
                $detenu = Detenu::create([
                    'nom'                    => $row['nom'],
                    'prenom'                 => $row['prenom'],
                    'nompere'                => $row['nompere'],
                    'nommere'                => $row['nommere'],
                    'adresse'                => $row['adresse'],
                    'cin'                    => $row['cin'],
                    'datenaissance'          => $this->transformDate($row['datenaissance']),
                    'numero_national_detenu' => $row['numero_detention_national'],
                    'nationalite_id'         => $row['nationality'] ?? 99,
                ]);

                // 2. Préparation des données du Dossier
                $dossierData = [
                    'numero_dapg'         => $row['numero_dossier'],
                    'date_sortie'         => $this->transformDate($row['datefin_peine']),
                    'date_enregistrement' => now()->format('Y-m-d H:i:s.v'),
                    'typedossier_id'      => $row['typedossier_id'],
                    'naturedossiers_id'   => $row['naturedossiers_id'],
                    'detenu_id'           => $detenu->id,
                    'user_id'             => $this->userId,
                    'originedossier'      => 'DAPG-ENCOURS',  // ← Spécifique à cet import
                    'user_tribunal_id'   => $row['tribunal_requette'],

                ];

                // Logique spécifique selon le type de dossier
                if ($row['typedossier_id'] == 1) {
                    if ($row['naturedossiers_id'] == 1) {
                        $dossierData['prison_id']         = $row['prison_id'];
                        $dossierData['numero_detention']  = $row['numero_detention_local'];
                    } else {
                        $dossierData['objetdemande_id'] = $row['objetdemande_id'];
                    }
                } elseif ($row['typedossier_id'] == 2) {
                    $dossierData['prison_id']        = $row['prison_id'];
                    $dossierData['numero_detention'] = $row['numero_detention_local'];
                }

                $dossier = Dossier::create($dossierData);

                // 3. Traitement des Affaires
                $affaireTribunaux    = !empty($row['tribunalaffaire']) ? explode(':', $row['tribunalaffaire']) : [];
                $affaireNumerosBruts = !empty($row['numeroaffaire'])   ? explode(':', $row['numeroaffaire'])   : [];
                $affaireDates        = !empty($row['datejujement'])    ? explode(':', $row['datejujement'])    : [];
                $affaireContenus     = !empty($row['conenujugement'])  ? explode(':', $row['conenujugement'])  : [];

                $affaireIds = [];
                foreach ($affaireNumerosBruts as $index => $numeroComplet) {
                    $numeroComplet = trim($numeroComplet);
                    if (empty($numeroComplet)) continue;

                    $segments = explode('/', $numeroComplet);
                    $annee  = null;
                    $code   = null;
                    $numero = null;

                    if (count($segments) === 3) {
                        $annee  = trim($segments[0]);
                        $code   = trim($segments[1]);
                        $numero = trim($segments[2]);
                    } elseif (count($segments) === 2) {
                        $annee  = trim($segments[0]);
                        $numero = trim($segments[1]);
                    } else {
                        $numero = $numeroComplet;
                    }

                    $affaire = Affaire::create([
                        'annee'          => $annee,
                        'code'           => $code,
                        'numero'         => $numero,
                        'datejujement'   => $this->transformDate($affaireDates[$index] ?? null),
                        'tribunal_id'    => trim($affaireTribunaux[$index] ?? 119),
                        'conenujugement' => trim($affaireContenus[$index] ?? null),
                        'numeroaffaire'  => $numeroComplet,
                    ]);

                    $affaireIds[] = $affaire->id;
                }

                if (!empty($affaireIds)) {
                    $dossier->affaires()->sync($affaireIds);
                }

                // Pas de création de Requette ici — c'est intentionnel
                $this->nbImportees++;
            });
        }

        // Mise à jour du log avec les stats finales
        if ($this->importLogId) {
            ImportEncoursLog::where('id', $this->importLogId)->update([
                'nb_lignes_total'     => $this->nbTotal,
                'nb_lignes_importees' => $this->nbImportees,
                'nb_lignes_ignorees'  => $this->nbTotal - $this->nbImportees,
                'statut'              => 'SUCCES',
            ]);
        }
    }

    /**
     * Gère les années (ex: 1950 → 1950-01-01) et les valeurs 0 / vides
     */
    private function transformDate($value)
    {
        $value = trim((string) $value);

        if (empty($value) || $value === '0') {
            return null;
        }

        // Année seule (4 chiffres)
        if (is_numeric($value) && strlen($value) === 4) {
            return $value . '-01-01';
        }

        // Numérique Excel (serial date)
        if (is_numeric($value) && $value > 10000) {
            return Carbon::instance(
                \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
            )->format('Y-m-d');
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
