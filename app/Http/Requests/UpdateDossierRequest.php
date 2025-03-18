<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDossierRequest extends FormRequest
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
            'nompere' => 'nullable',
            'nommere' => 'nullable',
            'cin' => 'min:4|max:12',
            'genre' => 'required',
            'nationalite' => 'nullable',
            'numeromp' => 'required',
            'typedossier' => 'required',
            'naturedossier' => 'required',
            'sourcedemande' => 'required',
            'objetdemande' => 'nullable',
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
            'affaires.*.copie_decision' => 'file|mimes:jpg,jpeg,png,pdf|max:2048', // Each file must be valid
            'affaires.*.copie_non_recours' => 'file|mimes:jpg,jpeg,png,pdf|max:2048', // Each file must be valid*/

            'copie_decision' => 'nullable|array',
            'copie_decision.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048', // Each file must be valid
            'copie_non_recours' => 'nullable|array',
            'copie_non_recours.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048', // Each file must be valid
            'copie_cin' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'prison' => 'nullable',
            'numerolocal' => 'nullable|numeric',
        ];
    }
}
