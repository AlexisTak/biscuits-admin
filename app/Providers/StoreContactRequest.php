<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'fingerprint' => $this->generateFingerprint(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s\-\'\.]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'service' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'string', 'max:0'],
            'ip_address' => ['sometimes', 'ip'],
            'user_agent' => ['sometimes', 'string', 'max:500'],
            'fingerprint' => ['sometimes', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'name.min' => 'Le nom doit contenir au moins 2 caractères',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email n\'est pas valide',
            'country.required' => 'Le pays est obligatoire',
            'service.required' => 'Le service est obligatoire',
            'message.required' => 'Le message est obligatoire',
            'message.min' => 'Le message doit contenir au moins 20 caractères',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Les données fournies sont invalides',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    public function isSpam(): bool
    {
        return $this->filled('website') && !empty($this->input('website'));
    }

    private function generateFingerprint(): string
    {
        return hash('sha256', implode('|', [
            $this->ip(),
            $this->userAgent(),
            date('Y-m-d'),
        ]));
    }
}