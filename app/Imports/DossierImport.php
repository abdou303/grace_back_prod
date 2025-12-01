<?php

namespace App\Imports;

use App\Models\Dossier;
use App\Models\Detenu;
use App\Models\Affaire;
use App\Models\Garant;
use App\Models\Peine;
use App\Models\Prison;
use App\Models\Requette;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DossierImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        //$rows->shift();
        foreach ($rows as $row) {


            // Search for existing dossier by numero_dapg
            $existingDossier = Dossier::where('numero_dapg', $row['numero_dossier'])->first();

            if ($existingDossier) {
                // Just create the Requette for existing dossier
                Requette::create([
                    'date_importation' => now()->format('Y-m-d H:i:s.v'),
                    'etat' => "NT",
                    'etat_tribunal' => "NT",
                    'observations' => $row['observation_requette'],
                    'typerequette_id' => $row['type_requette'],
                    'tribunal_id' => $row['tribunal_requette'],
                    'dossier_id' => $existingDossier->id
                ]);
            } else {




                // Find or create the detenu
                $detenu = Detenu::create([
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'nompere' => $row['nompere'],
                    'nommere' => $row['nommere'],
                    'cin' => $row['cin'],
                    'datenaissance' => $row['datenaissance'],                
					'nationalite_id' => $row['nationality'] ?? '100',
                    // 'datenaissance' => Carbon::createFromFormat('d/m/Y', $row['datenaissance'])->format('Y-m-d'),




                ]);

                // Create the Comportement

                // Create the Type Dossier 


                // Create the Dossier
                if ($row['typedossier_id'] == 1) {
                    if ($row['naturedossiers_id'] == 1) {



                        $dossier = Dossier::create([
                            'numero_dapg' => $row['numero_dossier'],
                            'date_sortie' => $row['datefin_peine'],
                            'date_enregistrement' => now()->format('Y-m-d H:i:s.v'),
                            'typedossier_id' => $row['typedossier_id'],
                            /*'typemotifdossiers_id' => $row['typemotifdossiers_id'],
                            'categoriedossiers_id' => $row['categoriedossiers_id'],*/
                            'naturedossiers_id' => $row['naturedossiers_id'],
                            'detenu_id' => $detenu->id,
                            'prison_id' => $row['prison_id'],
                            'user_id' => $row['user_id'],
                            'numero_detention' => $row['numero_detention_local'],

                        ]);
                    } else {


                        $dossier = Dossier::create([
                            'numero_dapg' => $row['numero_dossier'],
                            'date_sortie' => $row['datefin_peine'],
                            'date_enregistrement' => now()->format('Y-m-d H:i:s.v'),
                            'typedossier_id' => $row['typedossier_id'],
                            /*'typemotifdossiers_id' => $row['typemotifdossiers_id'],
                            'categoriedossiers_id' => $row['categoriedossiers_id'],*/
                            'naturedossiers_id' => $row['naturedossiers_id'],
                            'detenu_id' => $detenu->id,
                            'objetdemande_id' => $row['objetdemande_id'],
                            'user_id' => $row['user_id'],


                        ]);
                    }
                } elseif ($row['typedossier_id'] == 2) {

                    $dossier = Dossier::create([
                        'numero_dapg' => $row['numero_dossier'],
                        'date_sortie' => $row['datefin_peine'],
                        //'date_enregistrement' =>  now(),
                        'date_enregistrement' => now()->format('Y-m-d H:i:s.v'),
                        /*'avis_mp' =>  $row['avis_mp'],
                        'avis_dgapr' =>  $row['avis_dgapr'],
                        'avis_gouverneur' =>  $row['avis_gouverneur'],*/
                        'typedossier_id' => $row['typedossier_id'],
                        /* 'typemotifdossiers_id' => $row['typemotifdossiers_id'],
                        'categoriedossiers_id' => $row['categoriedossiers_id'],*/
                        'naturedossiers_id' => $row['naturedossiers_id'],
                        'detenu_id' => $detenu->id,
                        'prison_id' => $row['prison_id'],
                        'numero_detention' => $row['numero_detention_local'],
                        'user_id' => $row['user_id'],

                    ]);

                    /*$garant = Garant::create([
                    'nom' => $row['nom_garant'],
                    'prenom' => $row['prenom_garant'],
                    'adresse' => $row['adresse_garant'],
                    'qualite' => $row['qualite_garant'],
                    'province_id' => $row['garant_province_id'],
                    'tribunal_id' => $row['garant_tribunal_id'],

                ]);

                $dossier->garants()->syncWithoutDetaching([$garant->id]);*/
                }

                // Create or fetch Affaires and attach to the Dossier
                $affaireTribunal = array_map('trim', explode(':', $row['tribunalaffaire']));
                $affaireNumeros = array_map('trim', explode(':', $row['numeroaffaire']));
                $affaireDatesJugement = array_map('trim', explode(':', $row['datejujement']));
                $affaireConenuJugement = array_map('trim', explode(':', $row['conenujugement']));



                $affaireIds = [];
                foreach ($affaireNumeros as $index => $numeroAffaire) {
                    // Ensure there's a corresponding date for each numeroAffaire
                    $dateJugement = $affaireDatesJugement[$index] ?? null;
                    $contenuJugement = $affaireConenuJugement[$index] ?? null;
                    $tribunalJugement = $affaireTribunal[$index] ?? null;


                    $affaire = Affaire::firstOrCreate(['numeroaffaire' => $numeroAffaire, 'datejujement' => $dateJugement, 'conenujugement' => $contenuJugement, 'tribunal_id' => $tribunalJugement]);
                    $affaireIds[] = $affaire->id;
                }
                $dossier->affaires()->sync($affaireIds);


                /** Generer les Requettes predéfinées  */

                $requette = Requette::create([

                    'date_importation' => now()->format('Y-m-d H:i:s.v'),
                    'etat' => "NT",
                    'etat_tribunal' => "NT",
                    'observations' => $row['observation_requette'],
                    'typerequette_id' => $row['type_requette'],
                    'tribunal_id' => $row['tribunal_requette'],
                    'dossier_id' => $dossier->id

                ]);
            }
        }
    }
}
