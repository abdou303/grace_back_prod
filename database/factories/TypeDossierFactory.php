<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeDossier>
 */
class TypeDossierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types =
        ['type1'
        , 'type2'
]; 
        return [
            'libelle' => fake()->unique()->randomElement($types),
        
            'active' => fake()->randomElement([0, 1]),
        ];
    }
}
