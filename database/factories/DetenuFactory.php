<?php

namespace Database\Factories;

use App\Models\Nationalite;
use App\Models\Profession;
use App\Models\Ville;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Detenu>
 */
class DetenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $professionIds = Profession::pluck('id');
        $nationaliteIds = Nationalite::pluck('id');
        $villeIds = Ville::pluck('id');
        $genres = ["M", "F"];

        return [
            'nom' => fake()->firstName(),
            'prenom' => fake()->lastName(),
            'nompere' => fake()->firstName('male') . ' ' . fake()->lastName(),
            'nommere' => fake()->firstName('female') . ' ' . fake()->lastName(),
            'cin' => fake()->regexify('[A-Z]{2}[0-4]{4}'),
            'adresse' => fake()->address(),
            'datenaissance' => fake()->date('Y-m-d'),
            'profession_id' => fake()->randomElement($professionIds),
            'nationalite_id' => 99,
            'ville_id' => fake()->randomElement($villeIds),
            'genre' => fake()->randomElement($genres)
        ];
    }
}
