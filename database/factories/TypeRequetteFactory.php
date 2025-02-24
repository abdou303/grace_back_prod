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
        /* $typesrequettes =
            [
                'حول مآل النقض',
                ' البطاقة الوطنية للتعريف للمعتقلين',
                'حول مآل حكم غيابي',
                'حول التأكد من التقادم',
                'حول القضية موضوع البحث'
            ];*/

        $typesrequettes = [
            /* ['code' => 'NEW-GRACE-DOSSIER-ND', 'libelle' => 'طلب تهيئ ملف العفو سراح', 'min_pjs' => 5],
            ['code' => 'NEW-GRACE-DOSSIER-DT', 'libelle' => 'طلب تهيئ ملف العفو معتقلين', 'min_pjs' => 4],*/
            ['code' => 'NEW-GRACE-DOSSIER', 'libelle' => 'طلب تهيئ ملف العفو ', 'min_pjs' => 4],
            ['code' => 'NEW-LC-DOSSIER', 'libelle' => 'طلب تهيئ ملف الافراج المقيد بشروط ', 'min_pjs' => 4],
            ['code' => 'COMPLIMENT-DOSSIER', 'libelle' => 'استكمال تجهيز الملف ', 'min_pjs' => 1],
        ];


        $randomStatus = fake()->unique()->randomElement($typesrequettes);

        /*return [
            'libelle' => fake()->unique()->randomElement($typesrequettes),
            'active' => fake()->randomElement([0, 1]),
        ];*/

        return [
            'libelle' => $randomStatus['libelle'],
            'code' => $randomStatus['code'],
            'min_pjs' => $randomStatus['min_pjs'],
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
