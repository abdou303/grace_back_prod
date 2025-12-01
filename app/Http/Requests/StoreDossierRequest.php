<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDossierRequest extends FormRequest
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
            'nom' => 'required',
            'prenom' => 'required',
            'datenaissance' => 'nullable',
            //  'adresse' => 'nullable',

            'nompere' => 'nullable',
            'nommere' => 'nullable',
            'cin' => 'min:2|max:50',
            'genre' => 'required',
            'nationalite' => 'nullable',
            'numeromp' => 'nullable',
            'typedossier' => 'required',
            'naturedossier' => 'required',
            'sourcedemande' => 'required',
            'objetdemande' => 'nullable',
            'tribunal_user_libelle' => 'nullable',
            'user_id' => 'required',
            'tribunal_user_id' => 'nullable',
            'affaires' => 'required|array|min:1|max:8',
            'affaires.*.numero' => 'required|string',
            'affaires.*.code' => 'required|string',
            'affaires.*.annee' => 'required|string',
            'affaires.*.tribunal' => 'required|numeric',
            'affaires.*.datejujement' => 'required|string',
            'affaires.*.conenujugement' => 'nullable|string',
            'affaires.*.copie_decision' => 'file|mimes:pdf|max:2048', // Each file must be valid
            'affaires.*.copie_non_recours' => 'file|mimes:pdf|max:2048', // Each file must be valid
            'copie_cin' => 'nullable|file|mimes:pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:pdf|max:2048',
            'prison' => 'nullable',
            'numerolocal' => 'nullable|numeric',
            /* 'copie_decision' => 'nullable',
            'copie_cin' => 'nullable',
            'copie_mp' => 'nullable',
            'copie_non_recours' => 'nullable',
            'copie_social' => 'nullable',*/
            /*'copie_decision' => 'nullable|file|mimes:pdf|max:2048',
            'copie_non_recours' => 'nullable|file|mimes:pdf|max:2048',*/


        ];
    }

    public function messages(): array
{
    return [
        // Champs principaux
        'nom.required' => ' الاسم العائلي إجباري.',
        'prenom.required' => ' الاسم الشخصي إجباري.',
        'cin.min' => 'رقم البطاقة الوطنية يجب أن يحتوي على حرفين على الأقل.',
        'cin.max' => 'رقم البطاقة الوطنية لا يجب أن يتجاوز 50 حرفًا.',
        'genre.required' => ' الجنس إجباري.',
        'typedossier.required' => 'نوع الملف إجباري.',
        'naturedossier.required' => 'طبيعة الملف إجبارية.',
        'sourcedemande.required' => 'مصدر الطلب إجباري.',
        'user_id.required' => 'معرف المستخدم إجباري.',
        'numerolocal.numeric' => 'رقم الاعتقال المحلي يجب أن يكون رقمًا.',

        // Validation des affaires
        'affaires.required' => 'يجب إدخال على الأقل قضية واحدة.',
        'affaires.array' => 'القضايا يجب أن تكون على شكل قائمة.',
        'affaires.min' => 'يجب إدخال على الأقل قضية واحدة.',
        'affaires.max' => 'لا يمكن إدخال أكثر من 8 قضايا.',

        'affaires.*.numero.required' => 'رقم القضية إجباري.',
        'affaires.*.numero.string' => 'رقم القضية يجب أن يكون نصاً.',

        'affaires.*.code.required' => 'رمز القضية إجباري.',
        'affaires.*.code.string' => 'رمز القضية يجب أن يكون نصاً.',

        'affaires.*.annee.required' => 'سنة القضية إجبارية.',
        'affaires.*.annee.string' => 'سنة القضية يجب أن تكون نصاً.',

        'affaires.*.tribunal.required' => 'المحكمة إجبارية.',
        'affaires.*.tribunal.numeric' => 'المحكمة يجب أن تكون رقماً.',

        'affaires.*.datejujement.required' => 'تاريخ الحكم إجباري.',
        'affaires.*.datejujement.string' => 'تاريخ الحكم يجب أن يكون نصاً.',

        'affaires.*.conenujugement.string' => 'محتوى الحكم يجب أن يكون نصاً.',

        'affaires.*.copie_decision.file' => 'نسخة من المقرر القضائي يجب أن تكون ملفًا صالحًا.',
        'affaires.*.copie_decision.mimes' => 'نسخة من المقرر القضائي يجب أن تكون بصيغة PDF.',
        'affaires.*.copie_decision.max' => 'حجم نسخة من المقرر القضائي لا يجب أن يتجاوز 2 ميغابايت.',

        

        'affaires.*.copie_non_recours.file' => 'شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن تكون ملفًا صالحًا.',
        'affaires.*.copie_non_recours.mimes' => 'شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به يجب أن تكون بصيغة PDF.',
        'affaires.*.copie_non_recours.max' => 'حجم شهادة ضبطية او مايفيد حيازة المقرر القضائي لقوة الشيء المقضي به لا يجب أن يتجاوز 2 ميغابايت.',

        // Pièces jointes générales
        'copie_cin.file' => 'نسخة من بطاقة التعريف الوطنية يجب أن تكون ملفًا صالحًا.',
        'copie_cin.mimes' => 'نسخة من بطاقة التعريف الوطنية يجب أن تكون بصيغة PDF.',
        'copie_cin.max' => 'نسخة من بطاقة التعريف الوطنية لا يجب أن تتجاوز 2 ميغابايت.',

        'copie_mp.file' => 'ملتمس النيابة العامة يجب أن تكون ملفًا صالحًا.',
        'copie_mp.mimes' => 'ملتمس النيابة العامة يجب أن تكون بصيغة PDF.',
        'copie_mp.max' => 'ملتمس النيابة العامة لا يجب أن تتجاوز 2 ميغابايت.',

        'copie_social.file' => 'البحث الاجتماعي يجب أن تكون ملفًا صالحًا.',
        'copie_social.mimes' => 'البحث الاجتماعي يجب أن تكون بصيغة PDF.',
        'copie_social.max' => 'البحث الاجتماعي لا يجب أن تتجاوز 2 ميغابايت.',
    ];
}
}
