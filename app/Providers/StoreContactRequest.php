<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Prépare les données avant validation
     */
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
            // Champs OBLIGATOIRES du formulaire Astro
            'name' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[\p{L}\s\-\'\.]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'service' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            
            // Champs OPTIONNELS (pour extensions futures)
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            
            // Anti-spam honeypot (champ caché)
            'website' => ['nullable', 'string', 'max:0'],
            
            // Métadonnées (ajoutées auto)
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
            'name.regex' => 'Le nom contient des caractères invalides',
            
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email n\'est pas valide',
            
            'country.required' => 'Le pays est obligatoire',
            'service.required' => 'Le service est obligatoire',
            
            'message.required' => 'Le message est obligatoire',
            'message.min' => 'Le message doit contenir au moins 20 caractères',
            'message.max' => 'Le message ne peut pas dépasser 2000 caractères',
        ];
    }

    /**
     * Handle validation failure
     */
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

    /**
     * Vérifie si spam (honeypot rempli)
     */
    public function isSpam(): bool
    {
        return $this->filled('website') && !empty($this->input('website'));
    }

    /**
     * Génère un fingerprint unique
     */
    private function generateFingerprint(): string
    {
        $data = implode('|', [
            $this->ip(),
            $this->userAgent(),
            date('Y-m-d'),
        ]);
        
        return hash('sha256', $data);
    }
}
