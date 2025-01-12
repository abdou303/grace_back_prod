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
        $comportements =
        [
            'سيرة 1',
           'سيرة 2',
           'سيرة 3',
           'سيرة 4'
        ];
    return [
        'libelle' => fake()->unique()->randomElement($comportements),
        'active' => fake()->randomElement([1, 1]),
    ];
    }
}
