<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StatutRequette>
 */
class StatutRequetteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $status = [
            ['code' => 'KO', 'libelle' => 'لم يتم الاطلاع عليه'],
            ['code' => 'VU', 'libelle' => 'تم الاطلاع عليه'],
            ['code' => 'TR', 'libelle' => 'في طور المعالجة'],
            ['code' => 'OK', 'libelle' => 'منجز']
        ];
        $libelle = "libelle";
        $code = "code";

        $randomStatus = fake()->unique()->randomElement($status);
        return [
            'libelle' => $randomStatus['libelle'],
            'code' => $randomStatus['code'],
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
