<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDossierGreffeRequest extends FormRequest
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

            'tribunal_user_libelle' => 'nullable',
            'user_id' => 'required',
            'tribunal_user_id' => 'nullable',
            /* 'affaires' => 'required|array|min:1|max:8',
            'affaires.*.numero' => 'required|string',
            'affaires.*.code' => 'required|string',
            'affaires.*.annee' => 'required|string',
            'affaires.*.tribunal' => 'required|numeric',
            'affaires.*.datejujement' => 'required|string',
            'affaires.*.conenujugement' => 'nullable|string',
            'affaires.*.copie_decision' => 'file|mimes:pdf|max:2048', // Each file must be valid
            'affaires.*.copie_non_recours' => 'file|mimes:pdf|max:2048', // Each file must be valid*/

            'copie_decision' => 'nullable|array',
            'copie_decision.*' => 'file|mimes:pdf|max:2048', // Each file must be valid
            'copie_non_recours' => 'nullable|array',
            'copie_non_recours.*' => 'file|mimes:pdf|max:2048', // Each file must be valid
            'copie_cin' => 'nullable|file|mimes:pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:pdf|max:2048',
            'prison' => 'nullable',
            'numerolocal' => 'nullable|numeric',
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

            'user_id.required' => 'معرف المستخدم مطلوب.',

            // fichiers multiples
            'copie_decision.*.file' => ' نسخة من المقرر القضائي يجب أن يكون ملفًا صالحًا.',
            'copie_decision.*.mimes' => 'نسخة من المقرر القضائي يجب أن يكون بصيغة PDF.',
            'copie_decision.*.max' => 'نسخة من المقرر القضائي 2 ميغابايت.',

            'copie_non_recours.*.file' => 'شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن يكون ملفًا صالحًا.',
            'copie_non_recours.*.mimes' => 'شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن يكون بصيغة PDF.',
            'copie_non_recours.*.max' => 'شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به لا يجب أن يتجاوز 2 ميغابايت.',

            // fichiers simples
            'copie_cin.file' => 'نسخة من بطاقة التعريف الوطنية يجب أن تكون ملفًا صالحًا.',
            'copie_cin.mimes' => 'نسخة من بطاقة التعريف الوطنية يجب أن تكون بصيغة PDF.',
            'copie_cin.max' => 'نسخة من بطاقة التعريف الوطنية لا يجب أن تتجاوز 2 ميغابايت.',

            'copie_mp.file' => 'ملتمس النيابة العامة يجب أن تكون ملفًا صالحًا.',
            'copie_mp.mimes' => 'ملتمس النيابة العامة يجب أن تكون بصيغة PDF.',
            'copie_mp.max' => 'ملتمس النيابة العامة لا يجب أن تتجاوز 2 ميغابايت.',

            'copie_social.file' => 'البحث الاجتماعي يجب أن تكون ملفًا صالحًا.',
            'copie_social.mimes' => 'البحث الاجتماعي يجب أن تكون بصيغة PDF.',
            'copie_social.max' => 'البحث الاجتماعي لا يجب أن تتجاوز 2 ميغابايت.',

            'numerolocal.numeric' => 'رقم الاعتقال المحلي يجب أن يكون رقمًا.',
        ];
    }
}
