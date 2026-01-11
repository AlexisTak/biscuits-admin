<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevisController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('📥 Nouvelle demande de devis', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'origin' => $request->header('Origin'),
            ]);

            // ✅ Validation stricte
            $validated = $request->validate([
                'name' => ['required', 'string', 'min:2', 'max:100'],
                'email' => ['required', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'address' => ['required', 'string', 'min:5', 'max:255'],
                'zip_code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
                'service' => ['required', 'string', 'max:255'],
                'budget' => ['nullable', 'string', 'max:50'],
                'message' => ['required', 'string', 'min:20', 'max:2000'],
                'honey' => ['nullable', 'string', 'max:0'],
                'timestamp' => ['nullable', 'integer'],
            ]);

            // ✅ Protection anti-spam : honeypot
            if (!empty($validated['honey'])) {
                Log::warning('🚫 Spam détecté (devis) - honeypot rempli', [
                    'ip' => $request->ip(),
                    'email' => $validated['email'],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Demande de devis envoyée avec succès !',
                ], 200);
            }

            // ✅ Rate limit UNIQUEMENT en production
            if (!app()->environment('local', 'development')) {
                $recentCount = Devis::where('ip_address', $request->ip())
                    ->where('created_at', '>=', now()->subHour())
                    ->count();

                if ($recentCount >= 5) {
                    Log::warning('🚫 Rate limit dépassé (devis)', [
                        'ip' => $request->ip(),
                        'count' => $recentCount,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Trop de demandes. Veuillez réessayer dans 1 heure.',
                    ], 429);
                }
            }

            DB::beginTransaction();
            
            try {
                // ✅ Créer ou récupérer le contact
                $contact = Contact::firstOrCreate(
                    ['email' => strtolower(trim($validated['email']))],
                    [
                        'name' => strip_tags($validated['name']),
                        'phone' => isset($validated['phone']) ? strip_tags($validated['phone']) : null,
                        'address' => strip_tags($validated['address']),
                        'zip_code' => strip_tags($validated['zip_code']),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'status' => 'pending',
                        'is_read' => false,
                    ]
                );

                // ✅ Créer le devis
                $devis = Devis::create([
                    'name' => strip_tags($validated['name']),
                    'email' => strtolower(trim($validated['email'])),
                    'phone' => isset($validated['phone']) ? strip_tags($validated['phone']) : null,
                    'address' => strip_tags($validated['address']),
                    'zip_code' => strip_tags($validated['zip_code']),
                    'service' => strip_tags($validated['service']),
                    'budget' => isset($validated['budget']) ? strip_tags($validated['budget']) : null,
                    'message' => strip_tags($validated['message']),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => 'pending',
                ]);

                DB::commit();

                Log::info('✅ Devis créé avec succès', [
                    'devis_id' => $devis->id,
                    'contact_id' => $contact->id,
                    'email' => $devis->email,
                    'service' => $devis->service,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Demande de devis envoyée avec succès ! Nous vous répondrons dans les 24 heures.',
                    'data' => [
                        'id' => $devis->id,
                    ],
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            Log::error('❌ Validation échouée (devis)', [
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Erreur serveur lors de la création du devis', [
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