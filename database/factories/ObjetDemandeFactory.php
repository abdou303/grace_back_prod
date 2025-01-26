<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ObjetDemande>
 */
class ObjetDemandeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
  
        $objets =
        [
            'من العقوبة الحبسية',
            'من الغرامة',
            'منهما معا',

        ];
    return [
        'libelle' => fake()->unique()->randomElement($objets),
        'active' => fake()->randomElement([1, 1]),
    ];
    }
}
