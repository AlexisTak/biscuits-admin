<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s\-\'\.]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:100'],
            'service' => ['required', 'string', 'max:150'],
            'budget' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'fingerprint' => hash('sha256', $this->ip() . $this->userAgent() . config('app.key')),
        ]);
    }
}