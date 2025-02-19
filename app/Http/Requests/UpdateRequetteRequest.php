<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class UpdateRequetteRequest extends FormRequest
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
        //Log::info('Request Data:', $this->all());

        return [
            'statutRequette' => 'required|exists:statut_requettes,code',
            'numeromp' => 'required',
            'copie_decision' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_cin' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_mp' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_non_recours' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'copie_social' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }
}
