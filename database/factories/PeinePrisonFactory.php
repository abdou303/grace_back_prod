<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Peine;
use App\Models\Prison;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PeinePrisonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $peineIds = Peine::pluck('id');
        $prisonIds = Prison::pluck('id');
        return [
           'prison_id' => fake()->randomElement($prisonIds),
            'peine_id' =>fake()->randomElement($peineIds), 
        ];
    }
}
