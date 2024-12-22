<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Dossier;
use App\Models\Affaire;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DossierAffaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $affaireIds = Affaire::pluck('id');
        $dossierIds = Dossier::pluck('id');
        return [
           'dossier_id' => fake()->randomElement($dossierIds),
            'affaire_id' =>fake()->randomElement($affaireIds), 
        ];
    }
}
