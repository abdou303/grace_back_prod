<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAntecedentDossierRequest extends FormRequest
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
            'numeromp' => 'nullable',
            'typedossier' => 'required',
            'naturedossier' => 'required',
            'sourcedemande' => 'required',
            'objetdemande' => 'nullable',
            'tribunal_user_libelle' => 'nullable',
            'user_id' => 'required',
            'tribunal_user_id' => 'nullable',
            'has_antecedent' => 'required',
            'antecedant_id' => 'required',
            'detenu_id' => 'required'


        ];
    }
}
