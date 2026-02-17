<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGreffeRequetteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'statutRequette' => 'required|exists:statut_requettes,code',
            'numeromp' => 'nullable',
            'user_tribunal' => 'required',
            // Validate single file fields
            'copie_cin' => 'nullable|file|mimes:pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:pdf|max:2048',
            'copie_cat2' => 'nullable|file|mimes:pdf|max:2048',


            // Validate multiple files for different affaires
            'copie_decision' => 'nullable|nullable|array',
            'copie_decision.*' => 'file|mimes:pdf|max:2048', // Each file must be valid

            'copie_non_recours' => 'nullable|array',
            'copie_non_recours.*' => 'file|mimes:pdf|max:2048', // Each file must be valid


            'affaires.*.has_non_recours' => ['required', 'boolean'],

            'affaires.*.numero_cassation' => [
                'required_if:affaires.*.has_non_recours,false',
                'nullable',
                'string'
            ],

            'affaires.*.numero_envoi_cassation' => [
                'required_if:affaires.*.has_non_recours,false',
                'nullable',
                'string'
            ],

            'affaires.*.date_envoi_cassation' => [
                'required_if:affaires.*.has_non_recours,false',
                'nullable',
                'date'
            ],
        ];
    }


    public function messages(): array
    {
        return [
            // Champs obligatoires
            'statutRequette.required' => 'حقل حالة العريضة مطلوب.',
            'statutRequette.exists' => 'قيمة حالة العريضة غير صالحة.',
            'numeromp.required' => 'رقم الملف (MP) مطلوب.',

            // Validation des fichiers simples
            'copie_cin.file' => 'نسخة من بطاقة التعريف الوطنية يجب أن تكون ملفًا صالحًا.',
            'copie_cin.mimes' => 'نسخة من بطاقة التعريف الوطنية يجب أن تكون بصيغة PDF.',
            'copie_cin.max' => 'حجم نسخة من بطاقة التعريف الوطنية لا يجب أن يتجاوز 2 ميغابايت.',

            'copie_mp.file' => 'ملتمس النيابة العامة يجب أن تكون ملفًا صالحًا.',
            'copie_mp.mimes' => 'ملتمس النيابة العامة يجب أن تكون بصيغة PDF.',
            'copie_mp.max' => 'حجم ملتمس النيابة العامة لا يجب أن يتجاوز 2 ميغابايت.',

            'copie_social.file' => 'البحث الاجتماعي يجب أن تكون ملفًا صالحًا.',
            'copie_social.mimes' => 'البحث الاجتماعي يجب أن تكون بصيغة PDF.',
            'copie_social.max' => 'حجم البحث الاجتماعي لا يجب أن يتجاوز 2 ميغابايت.',

            'copie_cat2.file' => 'الوثيقة المطلوبة يجب أن تكون ملفًا صالحًا.',
            'copie_cat2.mimes' => 'الوثيقة المطلوبة يجب أن تكون بصيغة PDF.',
            'copie_cat2.max' => 'حجم الوثيقة المطلوبة لا يجب أن يتجاوز 2 ميغابايت.',

            // Validation des fichiers multiples
            'copie_decision.array' => 'نسخة من المقرر القضائي يجب أن تكون قائمة من الملفات.',
            'copie_decision.*.file' => 'كل ملف في نسخة من المقرر القضائي يجب أن يكون ملفًا صالحًا.',
            'copie_decision.*.mimes' => 'كل ملف في نسخة من المقرر القضائي يجب أن يكون بصيغة PDF.',
            'copie_decision.*.max' => 'كل ملف في نسخة من المقرر القضائي لا يجب أن يتجاوز 2 ميغابايت.',

            'copie_non_recours.array' => 'شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن تكون قائمة من الملفات.',
            'copie_non_recours.*.file' => 'كل ملف في شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن يكون ملفًا صالحًا.',
            'copie_non_recours.*.mimes' => 'كل ملف في شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن يكون بصيغة PDF.',
            'copie_non_recours.*.max' => 'كل ملف في شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به لا يجب أن يتجاوز 2 ميغابايت.',

        ];
    }
}
