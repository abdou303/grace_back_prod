<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypeRequette>
 */
class TypeRequetteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $typesrequettes =
            [
                'حول مآل النقض',
                ' البطاقة الوطنية للتعريف للمعتقلين',
                'حول مآل حكم غيابي',
                'حول التأكد من التقادم',
                'حول القضية موضوع البحث'
            ];

        return [
            'libelle' => fake()->unique()->randomElement($typesrequettes),
            'active' => fake()->randomElement([0, 1]),
        ];
    }
}
