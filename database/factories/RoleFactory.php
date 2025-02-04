<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles =
            [
                'DAPG',
                'TR',
                'ADMIN'
            ];
        return [
            'libelle' => fake()->unique()->randomElement($roles),
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
