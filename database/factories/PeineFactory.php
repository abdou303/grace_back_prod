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
        $observations=["السجين يظهر تعاونا ملحوظا مع إدارة السجن",
        "يُبدي السجين سلوكا عدوانيا تجاه السجناء الآخرين",
        "السجين ملتزم بالقوانين والتعليمات داخل السجن",
        "السجين يحتاج إلى متابعة نفسية بسبب التوتر",
        "يُظهر السجين رغبة في تحسين سلوكه العام"];
        return [
            'datedebut' => $startDate,
            'datefin' => $endDate,
            'observation' => fake()->randomElement($observations),
        ];
    }
}
