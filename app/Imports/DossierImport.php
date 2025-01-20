<?php

namespace App\Imports;

use App\Models\Dossier;
use App\Models\Detenu;
use App\Models\Affaire;
use App\Models\Peine;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;


class DossierImport implements ToCollection
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
                'nompere' => $row['nompere'],
                'nommere' => $row['nommere'],

            ]);

            // Create the Comportement

            // Create the Type Dossier 


            // Create the Dossier
            $dossier = Dossier::create([
                'numero' => $row['numero'],
                'date_enregistrement' =>  now(),
                'avis_mp' =>  $row['avis_mp'],
                'avis_dgapr' =>  $row['avis_dgapr'],
                'avis_gouverneur' =>  $row['avis_gouverneur'],
                'typedossier_id' => $row['typedossier_id'],
                'detenu_id' => $detenu->id,

            ]);

            // Create the Peine
            $peine = Peine::create([
                'datedebut' => $row['numero'],
                'datefin' =>  now(),
                'datefin' =>  $row['avis_gouverneur'],
                'avis_mp' =>  $row['avis_mp'],
                'avis_dgapr' =>  $row['avis_dgapr'],
                'avis_gouverneur' =>  $row['avis_gouverneur'],
                'typedossier_id' => $row['typedossier_id'],
                'detenu_id' => $detenu->id,

            ]);
            // Handle the "Affaires" and attach them to the Dossier


            $affaire = Affaire::firstOrCreate([
                'numeromp' => $row['numeromp'],
                'numero' => $row['numero'],
                'code' => $row['code'],
                'annee' => $row['annee'],
                'datejujement' => $row['datejujement'],
                'conenujugement' => $row['conenujugement'],
                'nbrannees' => $row['nbrannees'],
                'nbrmois' => $row['nbrmois'],
                'nbrmois' => $row['nbrmois'],
                'peine_id' => $peine->id,
                'tribunal_id' => $row['tribunal_id'],

            ]);

            // Attach the Affaire to the Dossier
            $dossier->affaires()->syncWithoutDetaching([$affaire->id]);
        }
    }
}
