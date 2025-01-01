<?php

namespace Database\Factories;

use App\Models\Peine;
use App\Models\Tribunal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affaire>
 */
class AffaireFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array

    {

        $peineIds = Peine::pluck('id');
        $tribunalIds = Tribunal::pluck('id');
        $conenujugementLabels = ['حكمت محكمة الاستئناف بفاس على المتهم بالسجن المؤبد بعد إدانته بجريمة القتل العمد مع سبق الإصرار والترصد'
        , 'قضت المحكمة الابتدائية بمراكش بالسجن النافذ لمدة أربع سنوات على المتهم بتهمة تزوير مستندات رسمية واستعمالها'
        ,'أصدرت المحكمة الابتدائية بالدار البيضاء حكماً بالسجن لمدة ثلاث سنوات نافذة وغرامة مالية قدرها 5,000 درهم على المتهم بحيازة وترويج المخدرات'
        ,'قضت محكمة الجنايات بطنجة بإدانة المتهم بجريمة الاعتداء الجسدي المؤدي إلى عاهة مستديمة، وحكمت عليه بعشر سنوات سجناً نافذاً مع التعويض المدني للضحية'
    ,'أدانت المحكمة الابتدائية بالرباط المتهم بجريمة السرقة الموصوفة، وحكمت عليه بالسجن النافذ لمدة خمس سنوات وغرامة مالية قدرها 10,000 درهم'];


        return [
            'numeromp' => fake()->numberBetween(1000, 9999) . '/' . fake()->year,
            'numero' => fake()->numberBetween(1, 9999),
            'code' => fake()->numberBetween(2000, 3000),
            'annee' => fake()->numberBetween(1997, 2025),
            'datejujement' => fake()->date('Y-m-d'),
            'conenujugement' => fake()->randomElement($conenujugementLabels),
            'nbrannees' => fake()->numberBetween(1, 30),
            'nbrmois' => fake()->numberBetween(1, 12),
            'peine_id' => fake()->randomElement($peineIds),
            'tribunal_id' => fake()->randomElement($tribunalIds),
        ];
    }
}
