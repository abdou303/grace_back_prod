<?php

namespace Database\Factories;

use App\Models\Peine;
use App\Models\Tribunal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affaire>
 */
class AffaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array

    {

        $peineIds = Peine::pluck('id');
        $tribunalIds = Tribunal::pluck('id');


        return [
            'numeromp' => fake()->numberBetween(1000, 9999) . '/' . fake()->year,
            'numero' => fake()->numberBetween(1, 9999),
            'code' => fake()->numberBetween(2000, 3000),
            'annee' => fake()->numberBetween(1997, 2025),
            'datejujement' => fake()->date('Y-m-d'),
            'conenujugement' => fake()->sentence(3),
            'nbrannees' => fake()->numberBetween(1, 30),
            'nbrmois' => fake()->numberBetween(1, 12),
            'peine_id' => fake()->randomElement($peineIds),
            'tribunal_id' => fake()->randomElement($tribunalIds),
        ];
    }
}
