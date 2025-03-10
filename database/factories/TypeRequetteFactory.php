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
            /*['code' => 'NEW-GRACE-DOSSIER', 'libelle' => 'طلب تهيئ ملف العفو ', 'min_pjs' => 4],
            ['code' => 'NEW-LC-DOSSIER', 'libelle' => 'طلب تهيئ ملف الافراج المقيد بشروط ', 'min_pjs' => 4],
            ['code' => 'COMPLIMENT-DOSSIER', 'libelle' => 'استكمال تجهيز الملف ', 'min_pjs' => 1],*/

            ['cat' => 'CAT-1', 'code' => 'طلب تهيئ ملف', 'libelle' => 'طلب تهيئ ملف العفو ', 'min_pjs' => 4],
            ['cat' => 'CAT-1', 'code' => 'طلب تهيئ ملف', 'libelle' => 'طلب تهيئ ملف الافراج المقيد بشروط ', 'min_pjs' => 4],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'البطاقة الوطنية للتعريف - سراح', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول مآل النقض', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول التأكد من أداء الغرامة', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول مآل حكم غيابي', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول البحث الاجتماعي', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'طلب نسخة حكم أو قرار', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول التأكد من التقادم', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول القضية موضوع البحث', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول الوضع المادي', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول التأكد من وجود تنازل', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول ارجاع المبالغ', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول تصحيح البطاقة الوطنية', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول المدة التي قضاها رهن الاعتقال', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول التأكد من أداء التعويضات المدنية', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول نسخة قرار بعد النقض و الاحالة', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول طلب العفو', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'مآل الملف بمحكمة النقض', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول نتيجة الخبرة المنجزة', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'حول تصحيح خطأ مادي', 'min_pjs' => 1],
            ['cat' => 'CAT-2', 'code' => 'استكمال تجهيز الملف', 'libelle' => 'التأكد من استمرارية العلاقة الزوجية', 'min_pjs' => 1],

        ];


        $randomStatus = fake()->unique()->randomElement($typesrequettes);

        /*return [
            'libelle' => fake()->unique()->randomElement($typesrequettes),
            'active' => fake()->randomElement([0, 1]),
        ];*/

        return [
            'libelle' => $randomStatus['libelle'],
            'code' => $randomStatus['code'],
            'cat' => $randomStatus['cat'],
            'min_pjs' => $randomStatus['min_pjs'],
            'active' => fake()->randomElement([1, 1]),
        ];
    }
}
