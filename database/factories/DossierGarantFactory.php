<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Dossier;
use App\Models\Garant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DossierGarantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $garantIds = Garant::pluck('id');
        $dossierIds = Dossier::pluck('id');
        return [
           'dossier_id' => fake()->randomElement($dossierIds),
            'garant_id' =>fake()->randomElement($garantIds), 
        ];
    }
}
