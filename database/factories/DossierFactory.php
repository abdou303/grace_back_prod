<?php

namespace Database\Factories;

use App\Models\CategorieDossier;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Detenu;
use App\Models\NatureDossier;
use App\Models\TypeDossier;
use App\Models\TypeMotifDossier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dossier>
 */
class DossierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $detenuIds = Detenu::pluck('id');
        $typedossierIds = TypeDossier::pluck('id');
        $naturedossierIds = NatureDossier::pluck('id');
        $categoriedossierIds = CategorieDossier::pluck('id');
        $typemotifdossierIds = TypeMotifDossier::pluck('id');
        $genres = ["M", "F"];



        return [
            //
            'numero' => fake()->numberBetween(1000, 9999) . '/' . fake()->year,
            'date_enregistrement' => fake()->date('Y-m-d'),
            'observation' => fake()->sentence(3),
            'avis_mp' => fake()->sentence(1),
            'avis_dgapr' => fake()->sentence(1),
            'avis_gouverneur' => fake()->sentence(1),
            'typedossier_id' => fake()->randomElement($typedossierIds),
            'detenu_id' => fake()->randomElement($detenuIds),
            'naturedossiers_id' => fake()->randomElement($naturedossierIds),
            'categoriedossiers_id' => fake()->randomElement($categoriedossierIds),
            'typemotifdossiers_id' => fake()->randomElement($typemotifdossierIds),
            'genre' => fake()->randomElement($genres)





        ];
    }
}
