<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comportement>
 */
class ComportementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /* $comportements =
            [
                'عادية',
                'حسنة',
                'لابأس بها',
                'سيئة'
            ];
        return [
            'libelle' => fake()->unique()->randomElement($comportements),
            'active' => fake()->randomElement([1, 1]),
        ];*/

        static $comportements = [
            'عادية',
            'حسنة',
            'لابأس بها',
            'سيئة',
        ];
        $libelle = array_pop($comportements);

        return [
            'libelle' => $libelle,
            'active' => fake()->randomElement([1, 1]), // Always returns 1 in this case
        ];
    }
}
