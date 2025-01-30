<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SourceDemande>
 */
class SourceDemandeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sources = [
            ['id' => 1, 'libelle' => ' المعني بالأمر شخصيا (معتقل أو في حالة سراح)'],
            ['id' => 2, 'libelle' => 'الممثل القانوني (محامي)'],
            ['id' => 3, 'libelle' => ' احد افراد العائلة'],
            ['id' => 4, 'libelle' => ' أحد الأصدقاء '],


        ];

        $randomObjets = fake()->unique()->randomElement($sources);
        return [
            // 'id' => $randomObjets['id'],
            'libelle' => $randomObjets['libelle'],
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
