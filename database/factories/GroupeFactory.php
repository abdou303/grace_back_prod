<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Groupe>
 */
class GroupeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $groupes =
            [
                'USERS',
                'ADMINS',
                'SUPERADMINS'
            ];
        return [
            'libelle' => fake()->unique()->randomElement($groupes),

            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
