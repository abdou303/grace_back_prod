<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypePj>
 */
class TypePjFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $typesPj =
            [
                'نسخة من المقرر القضائي ',
                'نسخة من بطاقة التعريف الوطنية',
                'ملتمس النيابة العامة',
                'شهادة ضبطية',
                'البحث الاجتماعي',
            ];
        return [
            'libelle' => fake()->unique()->randomElement($typesPj),
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
