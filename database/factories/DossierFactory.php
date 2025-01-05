<?php

namespace Database\Factories;

use App\Models\CategorieDossier;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Detenu;
use App\Models\NatureDossier;
use App\Models\TypeDossier;
use App\Models\TypeMotifDossier;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dossier>
 */
class DossierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $detenuIds = Detenu::pluck('id');
        $typedossierIds = TypeDossier::pluck('id');
        $naturedossierIds = NatureDossier::pluck('id');
        $categoriedossierIds = CategorieDossier::pluck('id');
        $typemotifdossierIds = TypeMotifDossier::pluck('id');
        
        $avis=["نعم","لا"];
        $observations=["السجين يظهر تعاونا ملحوظا مع إدارة السجن",
        "يُبدي السجين سلوكا عدوانيا تجاه السجناء الآخرين",
        "السجين ملتزم بالقوانين والتعليمات داخل السجن",
        "السجين يحتاج إلى متابعة نفسية بسبب التوتر",
        "يُظهر السجين رغبة في تحسين سلوكه العام"];



        return [
            //
            'numero' => fake()->numberBetween(1000, 9999) . '/' . fake()->year,
            'date_enregistrement' => fake()->date('Y-m-d'),
            'observation' => fake()->randomElement($observations),
            'avis_mp' => fake()->randomElement($avis),
            'avis_dgapr' => fake()->randomElement($avis),
            'avis_gouverneur' => fake()->randomElement($avis),
            'typedossier_id' => fake()->randomElement($typedossierIds),
            'detenu_id' => fake()->randomElement($detenuIds),
            'naturedossiers_id' => fake()->randomElement($naturedossierIds),
            'categoriedossiers_id' => fake()->randomElement($categoriedossierIds),
            'typemotifdossiers_id' => fake()->randomElement($typemotifdossierIds),
           





        ];
    }
}
