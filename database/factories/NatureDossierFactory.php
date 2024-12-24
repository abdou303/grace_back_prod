<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NatureDossier>
 */
class NatureDossierFactory extends Factory
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
                'سراح',
                'معتقل',
                'المؤبد',
                'الاعدام',
                'الحفظ',
            ];
        return [
            'libelle' => fake()->unique()->randomElement($types),
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
