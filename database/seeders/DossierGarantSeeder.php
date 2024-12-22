<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dossier;
use App\Models\Garant;

class DossierGarantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $garantCount = Garant::count();
        Dossier::all()->each(function ($dossier) use ($garantCount) {
            $take = random_int(1, $garantCount);
            $dossierIds = Garant::inRandomOrder()->take($take)->get()->pluck('id');
            $dossier->garants()->sync($dossierIds);
        });
    }
}
