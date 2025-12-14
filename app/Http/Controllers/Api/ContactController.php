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
     * Store a newly created contact request.
     * 
     * @param StoreContactRequest $request
     * @return JsonResponse
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        try {
            // 1. Vérification anti-spam (honeypot)
            if ($request->isSpam()) {
                Log::warning('🚨 Tentative de spam détectée et bloquée', [
                    'ip' => $request->ip(),
                    'email' => $request->input('email'),
                ]);

                // Retourner "succès" pour ne pas alerter le bot
                return response()->json([
                    'success' => true,
                    'message' => 'Votre message a été envoyé avec succès !',
                ], 200);
            }

            // 2. Récupération des données validées
            $validatedData = $request->validated();

            // 3. Détection de doublons récents (même fingerprint dans les 5 dernières minutes)
            $recentDuplicate = Contact::where('fingerprint', $validatedData['fingerprint'])
                ->where('created_at', '>=', Carbon::now()->subMinutes(5))
                ->first();

            if ($recentDuplicate) {
                Log::warning('⚠️ Doublon détecté (même fingerprint)', [
                    'email' => $validatedData['email'],
                    'fingerprint' => $validatedData['fingerprint'],
                    'original_contact_id' => $recentDuplicate->id,
                ]);

                // Retourner succès mais ne pas créer de doublon
                return response()->json([
                    'success' => true,
                    'message' => 'Votre message a déjà été enregistré.',
                    'data' => [
                        'id' => $recentDuplicate->id,
                    ],
                ], 200);
            }

            // 4. Préparation des données pour la création
            $dataToCreate = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'country' => $validatedData['country'],
                'service' => $validatedData['service'],
                'address' => $validatedData['address'] ?? null,
                'zip_code' => $validatedData['zip_code'] ?? null,
                'message' => $validatedData['message'],
                'status' => 'pending',
                'priority' => 'normal',
                'is_read' => false,
                'ip_address' => $validatedData['ip_address'] ?? $request->ip(),
                'user_agent' => $validatedData['user_agent'] ?? $request->userAgent(),
                'fingerprint' => $validatedData['fingerprint'],
            ];

            // 5. Création du contact
            $contact = Contact::create($dataToCreate);

            // 6. Log de succès
            Log::info('✅ Contact créé avec succès', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
                'service' => $contact->service,
                'ip' => $request->ip(),
            ]);

            // 7. TODO: Envoyer notification email admin (optionnel)
            // Notification::route('mail', config('mail.admin_email'))
            //     ->notify(new NewContactNotification($contact));

            // 8. Réponse de succès
            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les 24 heures.',
                'data' => [
                    'id' => $contact->id,
                ],
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Erreur base de données
            Log::error('❌ Erreur base de données lors de la création du contact', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement. Veuillez réessayer.',
            ], 500);

        } catch (\Exception $e) {
            // Erreur générale
            Log::error('❌ Erreur serveur lors de la création du contact', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Message d'erreur adapté à l'environnement
            $errorMessage = config('app.debug')
                ? 'Erreur serveur: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'
                : 'Une erreur est survenue. Veuillez réessayer plus tard.';

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }

    /**
     * Liste tous les contacts (admin)
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $contacts = Contact::with([])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ]);
    }

    /**
     * Affiche un contact spécifique (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function show(Contact $contact): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $contact,
        ]);
    }

    /**
     * Met à jour un contact (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function update(Contact $contact): JsonResponse
    {
        $validated = request()->validate([
            'status' => 'sometimes|in:pending,processed,archived',
            'priority' => 'sometimes|in:low,normal,high',
            'notes' => 'nullable|string',
            'is_read' => 'sometimes|boolean',
        ]);

        $contact->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contact mis à jour avec succès',
            'data' => $contact,
        ]);
    }

    /**
     * Supprime un contact (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact supprimé avec succès',
        ]);
    }

    /**
     * Marque comme lu (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function markAsRead(Contact $contact): JsonResponse
    {
        $contact->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Contact marqué comme lu',
        ]);
    }

    /**
     * Archive un contact (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function archive(Contact $contact): JsonResponse
    {
        $contact->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Contact archivé avec succès',
        ]);
    }

    /**
     * Marque comme spam (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function markAsSpam(Contact $contact): JsonResponse
    {
        $contact->delete(); // Soft delete

        Log::warning('Contact marqué comme spam', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contact marqué comme spam',
        ]);
    }

    /**
     * Assigne un contact à un utilisateur (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function assign(Contact $contact): JsonResponse
    {
        // TODO: Implémenter l'assignation à un user
        return response()->json([
            'success' => true,
            'message' => 'Fonctionnalité à implémenter',
        ]);
    }

    /**
     * Ajoute une réponse à un contact (admin)
     * 
     * @param Contact $contact
     * @return JsonResponse
     */
    public function addResponse(Contact $contact): JsonResponse
    {
        $validated = request()->validate([
            'response' => 'required|string|max:2000',
        ]);

        // Ajouter la réponse aux notes
        $currentNotes = $contact->notes ?? '';
        $newNote = sprintf(
            "[%s] %s\n",
            now()->format('Y-m-d H:i:s'),
            $validated['response']
        );

        $contact->update([
            'notes' => $currentNotes . $newNote,
            'status' => 'processed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Réponse ajoutée avec succès',
            'data' => $contact,
        ]);
    }
}