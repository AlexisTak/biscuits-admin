<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    /**
     * API : Créer un contact depuis le formulaire public (Astro)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('🔥 Nouvelle soumission de contact', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'origin' => $request->header('Origin'),
            ]);

            // ✅ Validation stricte avec les bons noms de champs
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:100'],
                'email' => ['required', 'email', 'max:255'],
                'country' => ['required', 'string', 'max:100'],
                'service' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string', 'min:20', 'max:2000'],
                'honey' => ['nullable', 'string', 'max:0'],
                'timestamp' => ['nullable', 'integer'],
            ]);

            // ✅ Protection anti-spam : honeypot
            if (!empty($validated['honey'])) {
                Log::warning('🚫 Spam détecté - honeypot rempli', [
                    'ip' => $request->ip(),
                    'email' => $validated['email'],
                ]);

                // Répondre comme si tout était OK pour ne pas alerter les bots
                return response()->json([
                    'success' => true,
                    'message' => 'Message envoyé avec succès !',
                ], 200);
            }

            // ✅ Protection anti-spam : timestamp (formulaire rempli trop vite)
            if (isset($validated['timestamp'])) {
                $elapsed = time() - $validated['timestamp'];
                
                if ($elapsed < 3) { // Moins de 3 secondes
                    Log::warning('🚫 Spam détecté - formulaire rempli trop vite', [
                        'elapsed' => $elapsed,
                        'ip' => $request->ip(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Veuillez prendre le temps de remplir le formulaire.',
                    ], 422);
                }
            }

            // ✅ Vérification rate-limit par IP (max 3 par heure)
            $recentCount = Contact::where('ip_address', $request->ip())
                ->where('created_at', '>=', now()->subHour())
                ->count();

            if ($recentCount >= 3) {
                Log::warning('🚫 Rate limit dépassé', [
                    'ip' => $request->ip(),
                    'count' => $recentCount,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Trop de demandes. Veuillez réessayer dans 1 heure.',
                ], 429);
            }

            // ✅ Création du contact
            $contact = Contact::create([
                'name' => strip_tags($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'country' => strip_tags($validated['country']),
                'service' => strip_tags($validated['service']),
                'message' => strip_tags($validated['message']),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'pending',
                'is_read' => false,
            ]);

            Log::info('✅ Contact créé avec succès', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'service' => $contact->service,
            ]);

            // TODO: Envoyer email de notification (via queue)
            // dispatch(new SendContactNotification($contact));

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé avec succès ! Nous vous répondrons dans les 24 heures.',
                'data' => [
                    'id' => $contact->id,
                ],
            ], 201);

        } catch (ValidationException $e) {
            Log::error('❌ Validation échouée', [
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Erreur serveur lors de la création du contact', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
            ], 500);
        }
    }
}