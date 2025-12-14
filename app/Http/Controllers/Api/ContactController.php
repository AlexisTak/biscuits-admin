<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ContactController extends Controller
{
    /**
     * Store a newly created contact (PUBLIC - depuis Astro)
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            // 1. Anti-spam : honeypot
            if ($request->isSpam()) {
                Log::warning('🚨 Spam détecté (honeypot)', [
                    'ip' => $request->ip(),
                    'email' => $request->input('email'),
                ]);

                // Retourner succès pour ne pas alerter le bot
                return response()->json([
                    'success' => true,
                    'message' => 'Votre message a été envoyé avec succès !',
                ], 200);
            }

            // 2. Récupération des données validées
            $validated = $request->validated();

            // 3. Détection doublons (même fingerprint < 5 min)
            $recentDuplicate = Contact::where('fingerprint', $validated['fingerprint'])
                ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                ->first();

            if ($recentDuplicate) {
                Log::warning('⚠️ Doublon détecté', [
                    'email' => $validated['email'],
                    'original_id' => $recentDuplicate->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Votre message a déjà été enregistré.',
                    'data' => ['id' => $recentDuplicate->id],
                ], 200);
            }

            // 4. Création du contact
            $contact = Contact::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'country' => $validated['country'],
                'service' => $validated['service'],
                'address' => $validated['address'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
                'message' => $validated['message'],
                'status' => 'pending',
                'priority' => 'normal',
                'is_read' => false,
                'ip_address' => $validated['ip_address'],
                'user_agent' => $validated['user_agent'],
                'fingerprint' => $validated['fingerprint'],
            ]);

            // 5. Log succès
            Log::info('✅ Contact créé', [
                'id' => $contact->id,
                'email' => $contact->email,
                'service' => $contact->service,
                'ip' => $request->ip(),
            ]);

            // 6. TODO: Notification email admin
            // dispatch(new SendContactNotification($contact));

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les 24 heures.',
                'data' => ['id' => $contact->id],
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('❌ Erreur BDD création contact', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement. Veuillez réessayer.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('❌ Erreur serveur création contact', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $errorMsg = config('app.debug')
                ? $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'
                : 'Une erreur est survenue. Veuillez réessayer plus tard.';

            return response()->json([
                'success' => false,
                'message' => $errorMsg,
            ], 500);
        }
    }
}