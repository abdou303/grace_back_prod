<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dossier;
use App\Models\Affaire;

class DossierAffaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //



        $affaireCount = Affaire::count();
        Dossier::all()->each(function ($dossier) use ($affaireCount) {
            $take = random_int(1, $affaireCount);
            $dossierIds = Affaire::inRandomOrder()->take($take)->get()->pluck('id');
            $dossier->affaires()->sync($dossierIds);
        });
    }
}
