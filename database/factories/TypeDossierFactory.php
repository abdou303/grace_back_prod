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
            [
                'ملف عفو',
                'ملف افراج مقيد بشرووط'
            ];
        return [
            'libelle' => fake()->unique()->randomElement($types),
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
