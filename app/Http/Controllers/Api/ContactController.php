<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest; // ⭐ CRITIQUE: Importation du FormRequest
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Soumission formulaire contact depuis le frontend.
     * La validation et l'anti-spam sont gérés par StoreContactRequest.
     *
     * @param StoreContactRequest $request
     * @return JsonResponse
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        
        // 2. Préparation des données pour la création
        $dataToCreate = array_merge($validatedData, [
            'status' => 'pending',
        ]);
        
        // Note: Assurez-vous que les champs 'address', 'zip_code', 'service', 
        // 'ip_address', 'user_agent', 'fingerprint' sont dans le tableau $fillable de votre modèle Contact.

        try {
            // 3. Création du contact
            $contact = Contact::create($dataToCreate);

            Log::info('Contact créé et enregistré', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'ip' => $request->ip()
            ]);
            
            // 5. Réponse de succès
            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès !',
                'data' => ['id' => $contact->id]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur création contact (serveur)', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'data' => $dataToCreate
            ]);

            // Gestion de l'affichage de l'erreur en fonction de l'environnement
            $errorMessage = config('app.debug') 
                ? 'Erreur serveur: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'
                : 'Une erreur est survenue. Veuillez réessayer.';

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }
}