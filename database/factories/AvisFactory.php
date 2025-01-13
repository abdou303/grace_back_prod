<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Avis>
 */
class AvisFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $avis =
            [
                'لايرى مانعا',
                'الموافقة',
                'الرفض',
                'إسناد النظر',
                'التحفظ'
            ];
        return [
            'libelle' => fake()->unique()->randomElement($avis),
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
