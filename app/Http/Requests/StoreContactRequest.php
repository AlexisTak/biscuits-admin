<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s\-\'\.]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            
            'address' => ['required', 'string', 'max:255'], 
            'zip_code' => ['required', 'string', 'max:20'],
            
            'service' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:1000'],
            'honey' => ['nullable', 'max:0'], // Honeypot
            'timestamp' => ['required', 'integer', 'min:' . (time() - 3600)], 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.regex' => 'Le nom contient des caractères invalides.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'country.required' => 'Le pays est obligatoire.',
            'service.required' => 'Le service est obligatoire.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 10 caractères.',
            'timestamp.min' => 'Le formulaire a expiré. Veuillez recharger la page.',
        ];
    }

    /**
     * Préparer les données avec métadonnées de sécurité
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'fingerprint' => $this->generateFingerprint(),
        ]);
    }

    /**
     * Génère un fingerprint unique basé sur IP + User-Agent
     */
    private function generateFingerprint(): string
    {
        return hash('sha256', $this->ip() . $this->userAgent() . config('app.key'));
    }
}