<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Peine>
 */
class PeineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate start_date in the future
        $startDate = $this->faker->dateTimeBetween('now', '+1 year'); // Start date up to 1 year in the future

        // Calculate end_date with a custom interval
        $endDate = (clone $startDate)->modify('+' . fake()->numberBetween(7, 130) . ' months');

        $faker = FakerFactory::create('ar_SA'); // Arabic locale
        return [
            'datedebut' => $startDate,
            'datefin' => $endDate,
            'observation' => $faker->sentence(3)
        ];
    }
}
