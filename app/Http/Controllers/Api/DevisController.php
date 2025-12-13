<?php
// app/Http/Controllers/Api/DevisController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DevisController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'service' => ['required', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        try {
            // Créer ou récupérer le contact
            $contact = Contact::firstOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'service' => $validated['service'],
                    'country' => 'Non spécifié',
                    'message' => $validated['message'] ?? '',
                    'status' => 'pending',
                ]
            );

            // Créer le devis
            $devis = Devis::create([
                'contact_id' => $contact->id,
                'service' => $validated['service'],
                'amount' => 0,
                'status' => 'pending',
                'notes' => 'Téléphone: ' . ($validated['phone'] ?? 'Non fourni') . "\n" . ($validated['message'] ?? ''),
            ]);

            Log::info('Devis créé', [
                'devis_id' => $devis->id,
                'contact_id' => $contact->id,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Votre demande de devis a été envoyée avec succès !',
                'data' => ['id' => $devis->id]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur création devis', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue.',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage()
                ] : null
            ], 500);
        }
    }
}