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
        return false;
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
            'datenaissance' => '',
            'nompere' => '',
            'nommere' => '',
            'cin' => 'min:4|max:12',
            'genre' => 'required',
            'nationalite' => 'required',
            'affaires' => 'required|array|min:1|max:3',
            'affaires.*.numeromp' => 'required',
            'affaires.*.numero' => 'required|string',
            'affaires.*.code' => 'required|string',
            'affaires.*.annee' => 'required|string',
            'affaires.*.tribunal' => 'required|numeric',
            'copie_decision' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'copie_cin' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'copie_non_recours' => 'nullable|file|mimes:jpg,png,pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:jpg,png,pdf|max:2048',


        ];
    }
}
