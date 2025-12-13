<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:contacts,id']
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Aucun contact sélectionné',
            'ids.*.exists' => 'Contact invalide',
        ];
    }
}