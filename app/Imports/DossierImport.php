<?php

namespace App\Imports;

use App\Models\Dossier;
use App\Models\Detenu;
use App\Models\Affaire;
use App\Models\Peine;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DossierImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $rows->shift();
        foreach ($rows as $row) {

            // Find or create the detenu
            $detenu = Detenu::create([
                 'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'nompere' => $row['nompere'],
                'nommere' => $row['nommere'],
                'cin' => $row['cin'],
                'datenaissance' => $row['datenaissance'],
               // 'datenaissance' => Carbon::createFromFormat('d/m/Y', $row['datenaissance'])->format('Y-m-d'),

                


            ]);

            // Create the Comportement

            // Create the Type Dossier 


            // Create the Dossier
            $dossier = Dossier::create([
                'numero' => $row['numero_dossier'],
                'date_enregistrement' =>  now(),
                'avis_mp' =>  $row['avis_mp'],
                'avis_dgapr' =>  $row['avis_dgapr'],
                'avis_gouverneur' =>  $row['avis_gouverneur'],
                'typedossier_id' => $row['typedossier_id'],
                'typemotifdossiers_id'=> $row['typemotifdossiers_id'],
                'categoriedossiers_id'=>1,
                'naturedossiers_id'=>1,
                
                'detenu_id' => $detenu->id,

            ]);

            // Create the Peine
            $peine = Peine::create([
                'datedebut' => $row['datedebut_peine'],
                //'datedebut' =>Carbon::createFromFormat('d/m/Y', $row['datedebut_peine'])->format('Y-m-d'),
                'datefin' =>   $row['datefin_peine'],
             //   'datefin' =>Carbon::createFromFormat('d/m/Y', $row['datefin_peine'])->format('Y-m-d'),

            ]);
            // Handle the "Affaires" and attach them to the Dossier


            $affaire = Affaire::firstOrCreate([
                'numeromp' => $row['numeromp'],
                'numero' => $row['numero'],
                'code' => $row['code'],
                'annee' => $row['annee'],
                'datejujement' => $row['datejujement'],
                //'datejujement' =>Carbon::createFromFormat('d/m/Y', $row['datejujement'])->format('Y-m-d'),
                'conenujugement' => $row['conenujugement'],
                'nbrannees' => $row['nbrannees'],
                'nbrmois' => $row['nbrmois'],
                'peine_id' => $peine->id,
                'tribunal_id' => 119,

            ]);

            // Attach the Affaire to the Dossier
            $dossier->affaires()->syncWithoutDetaching([$affaire->id]);
        }
    }
}
