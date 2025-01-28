<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ObjetDemande>
 */
class ObjetDemandeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {


        $objets = [
            ['id' => 1, 'libelle' => 'من العقوبة الحبسية'],
            ['id' => 2, 'libelle' => 'من الغرامة'],
            ['id' => 3, 'libelle' => 'من العقوبة الحبسية ومن الغرامة معا'],

        ];
        /* $objets =
            [
                'من العقوبة الحبسية',
                'من الغرامة',
                'من العقوبة الحبسية ومن الغرامة معا',

            ];*/

        $randomObjets = fake()->unique()->randomElement($objets);
        return [
            // 'id' => $randomObjets['id'],
            'libelle' => $randomObjets['libelle'],
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
