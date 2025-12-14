<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Prépare les données avant validation
     */
    protected function prepareForValidation(): void
    {
        // Protection anti-spam : honeypot
        if ($this->filled('website')) {
            \Log::warning('🚨 Spam détecté via honeypot', [
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'honeypot_value' => $this->input('website'),
            ]);
        }

        // Génération du fingerprint (détection de doublons)
        $fingerprint = $this->generateFingerprint();

        // Ajout des métadonnées de sécurité
        $this->merge([
            'ip_address' => $this->ip(),
            'user_agent' => $this->userAgent(),
            'fingerprint' => $fingerprint,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Champs obligatoires du formulaire
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[a-zA-ZÀ-ÿ\s\'-]+$/u'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'country' => [
                'required',
                'string',
                'max:100'
            ],
            'service' => [
                'required',
                'string',
                'max:255'
            ],
            'message' => [
                'required',
                'string',
                'min:20',
                'max:2000'
            ],
            
            // Champs optionnels (pour extensions futures)
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\d\s\+\-\(\)]+$/'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'zip_code' => [
                'nullable',
                'string',
                'max:20'
            ],
            
            // Honeypot (champ caché anti-spam)
            'website' => [
                'nullable',
                'string'
            ],

            // Métadonnées (ajoutées automatiquement)
            'ip_address' => [
                'sometimes',
                'ip'
            ],
            'user_agent' => [
                'sometimes',
                'string',
                'max:500'
            ],
            'fingerprint' => [
                'sometimes',
                'string',
                'max:64'
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire',
            'name.min' => 'Le nom doit contenir au moins 2 caractères',
            'name.max' => 'Le nom ne peut pas dépasser 100 caractères',
            'name.regex' => 'Le nom contient des caractères invalides',
            
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email n\'est pas valide',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères',
            
            'country.required' => 'Le pays est obligatoire',
            
            'service.required' => 'Le service est obligatoire',
            
            'message.required' => 'Le message est obligatoire',
            'message.min' => 'Le message doit contenir au moins 20 caractères',
            'message.max' => 'Le message ne peut pas dépasser 2000 caractères',
            
            'phone.regex' => 'Le numéro de téléphone n\'est pas valide',
        ];
    }

    /**
     * Handle a failed validation attempt.
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
     * Vérifie si la requête est du spam (honeypot rempli)
     */
    public function isSpam(): bool
    {
        return $this->filled('website') && !empty($this->input('website'));
    }

    /**
     * Génère un fingerprint unique basé sur les données du formulaire
     * Utilisé pour détecter les doublons et le spam
     */
    private function generateFingerprint(): string
    {
        $data = implode('|', [
            $this->input('email', ''),
            $this->ip(),
            $this->userAgent(),
        ]);

        return hash('sha256', $data);
    }
}