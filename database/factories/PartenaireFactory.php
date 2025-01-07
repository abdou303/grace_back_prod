<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partenaire>
 */
class PartenaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $partenaires =
            [
                'المندوبية العامة لإدارة السجون وإعادة الإدماج',
                'وزارة الداخلية'
            ];

        return [
            'libelle' => fake()->unique()->randomElement($partenaires),

            'active' => fake()->randomElement([0, 1]),
        ];
    }
}
