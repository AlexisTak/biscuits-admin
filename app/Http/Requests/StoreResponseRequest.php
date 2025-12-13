<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('respond', $this->route('contact') ?? $this->route('devis'));
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'is_internal_note' => ['sometimes', 'boolean'],
        ];
    }
}