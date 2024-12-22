<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Detenu;
use App\Models\TypeDossier;


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

   
        return [
            //
        'numero'=> fake()->numberBetween(1000, 9999) . '/' . fake()->year,
            'date_enregistrement'=> fake()->date('Y-m-d'),
            'observation'=> fake()->sentence(3),
            'avis_mp'=> fake()->sentence(1),
            'avis_dgapr'=> fake()->sentence(1),
            'avis_gouverneur'=> fake()->sentence(1),
            'typedossier_id'=> fake()->randomElement($typedossierIds),
            'detenu_id'=> fake()->randomElement($detenuIds)
        ];
    }
}
