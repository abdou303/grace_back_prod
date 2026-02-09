<?php

namespace App\Imports;

use App\Models\Dossier;
use App\Models\Detenu;
use App\Models\Affaire;
use App\Models\Requette;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB; // Important pour la sécurité des données
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DossierImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Utilisation d'une transaction pour garantir l'intégrité des données
            DB::transaction(function () use ($row) {

                $existingDossier = Dossier::where('numero_dapg', $row['numero_dossier'])->first();

                if ($existingDossier) {
                    // Si le dossier existe, on ajoute seulement la requête
                    $this->createRequette($existingDossier->id, $row);
                } else {
                    // 1. Création du Détenu
                    $detenu = Detenu::create([
                        'nom' => $row['nom'],
                        'prenom' => $row['prenom'],
                        'nompere' => $row['nompere'],
                        'nommere' => $row['nommere'],
                        'cin' => $row['cin'],
                        'datenaissance' => $row['datenaissance'],
                        'numero_national_detenu' => $row['numero_detention_national'],
                        'nationalite_id' => $row['nationality'] ?? '100',

                    ]);

                    // 2. Préparation des données du Dossier
                    $dossierData = [
                        'numero_dapg' => $row['numero_dossier'],
                        'date_sortie' => $row['datefin_peine'],
                        'date_enregistrement' => now()->format('Y-m-d H:i:s.v'),
                        'typedossier_id' => $row['typedossier_id'],
                        'naturedossiers_id' => $row['naturedossiers_id'],
                        'detenu_id' => $detenu->id,
                        'user_id' => $row['user_id'],
                        'originedossier' => 'R'
                    ];

                    // Logique spécifique selon le type de dossier
                    if ($row['typedossier_id'] == 1) {
                        if ($row['naturedossiers_id'] == 1) {
                            $dossierData['prison_id'] = $row['prison_id'];
                            $dossierData['numero_detention'] = $row['numero_detention_local'];
                        } else {
                            $dossierData['objetdemande_id'] = $row['objetdemande_id'];
                        }
                    } elseif ($row['typedossier_id'] == 2) {
                        $dossierData['prison_id'] = $row['prison_id'];
                        $dossierData['numero_detention'] = $row['numero_detention_local'];
                    }

                    $dossier = Dossier::create($dossierData);

                    // 3. Traitement des Affaires (Logique de split demandée)
                    $affaireTribunaux = array_map('trim', explode(':', $row['tribunalaffaire']));
                    $affaireNumerosBruts = array_map('trim', explode(':', $row['numeroaffaire'])); // Format "2025/2601/123"
                    $affaireDates = array_map('trim', explode(':', $row['datejujement']));
                    $affaireContenus = array_map('trim', explode(':', $row['conenujugement']));



                    $affaireIds = [];
                    foreach ($affaireNumerosBruts as $index => $numeroComplet) {
                        if (empty($numeroComplet)) continue;

                        // SPLIT DU NUMERO D'AFFAIRE (Ex: 2025/2601/123)
                        $segments = explode('/', $numeroComplet);

                        $annee = null;
                        $code = null;
                        $numero = null;

                        if (count($segments) === 3) {
                            // Format: 2025/2601/123
                            $annee = $segments[0];
                            $code  = $segments[1];
                            $numero = $segments[2];
                        } elseif (count($segments) === 2) {
                            // Format: 2025/123
                            $annee = $segments[0];
                            $numero = $segments[1];
                        }

                        // Création ou récupération de l'affaire avec les nouvelles colonnes
                        $affaire = Affaire::create([
                            'annee'           => $annee,
                            'code'            => $code,
                            'numero'          => $numero,
                            'datejujement'    => $affaireDates[$index] ?? null,
                            'tribunal_id'     => $affaireTribunaux[$index] ?? null,
                            'conenujugement'  => $affaireContenus[$index] ?? null,
                            'numeroaffaire' => $affaireNumerosBruts[$index] ?? null,
                        ]);

                        $affaireIds[] = $affaire->id;
                    }

                    $dossier->affaires()->sync($affaireIds);

                    // 4. Création de la requête initiale
                    $this->createRequette($dossier->id, $row);
                }
            });
        }
    }

    /**
     * Fonction Helper pour éviter la répétition de création de requête
     */
    private function createRequette($dossierId, $row)
    {
        return Requette::create([
            'date_importation' => now()->format('Y-m-d H:i:s.v'),
            'etat' => "NT",
            'etat_tribunal' => "NT",
            'observations' => $row['observation_requette'],
            'typerequette_id' => $row['type_requette'],
            'tribunal_id' => $row['tribunal_requette'],
            'dossier_id' => $dossierId
        ]);
    }
}
