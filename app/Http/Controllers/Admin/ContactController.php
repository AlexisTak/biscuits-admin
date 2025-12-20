<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    /**
     * Afficher la liste des contacts avec filtres et recherche
     */
    public function index(Request $request): View
    {
        // ⚠️ LIGNE CORRIGÉE : Relation 'devis' retirée
        $query = Contact::query();

        // Recherche full-text sur plusieurs champs
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%")
                  ->orWhere('phone', 'ILIKE', "%{$search}%")
                  ->orWhere('address', 'ILIKE', "%{$search}%")
                  ->orWhere('country', 'ILIKE', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filtre par service
        if ($service = $request->input('service')) {
            $query->where('service', $service);
        }

        // Tri personnalisé
        $sortField = $request->input('sort', 'created_at');
        $sortOrder = $request->input('order', 'desc');
        
        $query->orderBy($sortField, $sortOrder);

        // Pagination avec conservation des paramètres de recherche
        $contacts = $query->paginate(20)->withQueryString();

        // Liste des services uniques pour le filtre
        $services = Contact::query()
            ->whereNotNull('service')
            ->distinct()
            ->pluck('service')
            ->sort()
            ->values();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'services' => $services,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'service' => $request->input('service'),
            ],
        ]);
    }

    /**
     * Afficher un contact et marquer comme lu
     */
    public function show(Contact $contact): View
    {
        // ⚠️ LIGNE CORRIGÉE : load('devis') retiré
        
        // Marquer comme lu si non lu
        if (!$contact->is_read) {
            $contact->update(['is_read' => true]);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Contact $contact): View
    {
        // Liste des services pour le formulaire d'édition
        $services = Contact::query()
            ->whereNotNull('service')
            ->distinct()
            ->pluck('service')
            ->sort()
            ->values();

        return view('admin.contacts.edit', [
            'contact' => $contact,
            'services' => $services,
        ]);
    }

    /**
     * Mettre à jour un contact
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'service' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', 'in:pending,processed,archived'],
            'priority' => ['nullable', 'in:low,normal,high'],
        ]);

        $contact->update($validated);

        Log::info('Contact mis à jour', [
            'contact_id' => $contact->id,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('success', 'Le contact a été mis à jour avec succès.');
    }

    /**
     * Mettre à jour uniquement le statut
     */
    public function updateStatus(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processed,archived'],
        ]);

        $oldStatus = $contact->status;
        $contact->update($validated);

        Log::info('Statut du contact mis à jour', [
            'contact_id' => $contact->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Le statut du contact a été mis à jour avec succès.');
    }

    /**
     * Ajouter une note interne avec horodatage et auteur
     */
    public function addNote(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        // Préfixe avec date, heure et auteur
        $timestamp = now()->format('d/m/Y H:i');
        $author = auth()->user()->name ?? 'Admin';
        $prefix = "[{$timestamp} - {$author}]";

        // Ajouter la nouvelle note
        $newNote = "{$prefix}: {$validated['note']}";
        
        $contact->notes = $contact->notes 
            ? "{$contact->notes}\n\n{$newNote}" 
            : $newNote;

        $contact->save();

        Log::info('Note ajoutée au contact', [
            'contact_id' => $contact->id,
            'added_by' => auth()->id(),
        ]);

        return back()->with('success', 'Note interne ajoutée avec succès.');
    }

    /**
     * Supprimer un contact (soft delete)
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        Log::info('Contact supprimé', [
            'contact_id' => $contact->id,
            'deleted_by' => auth()->id(),
        ]);

        $contact->delete();

        return redirect()
            ->route('admin.contacts.index')
            ->with('success', 'Contact supprimé avec succès.');
    }

    /**
     * Restaurer un contact supprimé
     */
    public function restore(int $id): RedirectResponse
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        $contact->restore();

        Log::info('Contact restauré', [
            'contact_id' => $contact->id,
            'restored_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.contacts.show', $contact)
            ->with('success', 'Contact restauré avec succès.');
    }

    /**
     * Actions en masse (archiver, supprimer, etc.)
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:archive,delete,mark_read'],
            'contact_ids' => ['required', 'array'],
            'contact_ids.*' => ['exists:contacts,id'],
        ]);

        $count = 0;

        DB::transaction(function () use ($validated, &$count) {
            $contacts = Contact::whereIn('id', $validated['contact_ids']);

            switch ($validated['action']) {
                case 'archive':
                    $count = $contacts->update(['status' => 'archived']);
                    Log::info("Contacts archivés en masse", ['count' => $count]);
                    break;

                case 'delete':
                    $count = $contacts->delete();
                    Log::info("Contacts supprimés en masse", ['count' => $count]);
                    break;

                case 'mark_read':
                    $count = $contacts->update(['is_read' => true]);
                    Log::info("Contacts marqués comme lus", ['count' => $count]);
                    break;
            }
        });

        return back()->with('success', "{$count} contact(s) traité(s) avec succès.");
    }

    /**
     * Export CSV des contacts
     */
    public function export(Request $request)
    {
        $query = Contact::query();

        // Appliquer les mêmes filtres que l'index
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($service = $request->input('service')) {
            $query->where('service', $service);
        }

        $contacts = $query->orderBy('created_at', 'desc')->get();

        $filename = 'contacts_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($contacts) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes CSV
            fputcsv($handle, [
                'ID',
                'Nom',
                'Email',
                'Téléphone',
                'Pays',
                'Service',
                'Adresse',
                'Code Postal',
                'Statut',
                'Priorité',
                'Lu',
                'Date de création',
                'Message',
                'Notes',
            ], ';');

            // Données
            foreach ($contacts as $contact) {
                fputcsv($handle, [
                    $contact->id,
                    $contact->name,
                    $contact->email,
                    $contact->phone ?? '',
                    $contact->country,
                    $contact->service ?? '',
                    $contact->address ?? '',
                    $contact->zip_code ?? '',
                    $contact->status,
                    $contact->priority ?? 'normal',
                    $contact->is_read ? 'Oui' : 'Non',
                    $contact->created_at->format('Y-m-d H:i:s'),
                    $contact->message,
                    $contact->notes ?? '',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Statistiques des contacts (pour dashboard)
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Contact::count(),
            'pending' => Contact::where('status', 'pending')->count(),
            'processed' => Contact::where('status', 'processed')->count(),
            'archived' => Contact::where('status', 'archived')->count(),
            'unread' => Contact::where('is_read', false)->count(),
            'today' => Contact::whereDate('created_at', today())->count(),
            'week' => Contact::where('created_at', '>=', now()->subWeek())->count(),
            'month' => Contact::where('created_at', '>=', now()->subMonth())->count(),
        ];

        return response()->json($stats);
    }

    /**
     * API : Créer un contact depuis le formulaire public (Astro)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('🔥 Nouvelle soumission de contact', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Validation stricte
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email:rfc,dns', 'max:255'],
                'country' => ['required', 'string', 'max:100'],
                'phone' => ['nullable', 'string', 'max:20'],
                'service' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string', 'min:10', 'max:2000'],
                'honey' => ['nullable', 'string', 'max:0'],
                'timestamp' => ['nullable', 'integer', 'min:' . (now()->timestamp - 600)],
            ]);

            // Protection anti-spam : honeypot
            if (!empty($validated['honey'])) {
                Log::warning('🚫 Spam détecté - honeypot rempli', [
                    'ip' => $request->ip(),
                    'email' => $validated['email'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Requête invalide.',
                ], 422);
            }

            // Protection anti-spam : timestamp (formulaire rempli trop vite)
            if (isset($validated['timestamp'])) {
                $elapsed = now()->timestamp - $validated['timestamp'];
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

            // Création du contact
            $contact = Contact::create([
                'name' => strip_tags($validated['name']),
                'email' => strtolower(trim($validated['email'])),
                'country' => strip_tags($validated['country']),
                'phone' => isset($validated['phone']) ? strip_tags($validated['phone']) : null,
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
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }
}