<?php
// app/Services/Admin/ContactService.php

namespace App\Services\Admin;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service Contact Admin
 * 
 * Responsabilité: Logique métier pour la gestion des contacts
 * - Filtrage et pagination
 * - Changement de statut
 * - Suppression avec logs
 * - Suppression en masse
 */
class ContactService
{
    /**
     * Récupération des contacts avec filtres et pagination
     * 
     * @param array $filters Filtres: search, status, service, date_from, date_to
     * @param int $perPage Nombre d'éléments par page
     * @return LengthAwarePaginator
     */
    public function getFilteredContacts(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Contact::query();

        // Filtre recherche (nom ou email)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtre statut
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filtre service
        if (!empty($filters['service'])) {
            $query->where('service', $filters['service']);
        }

        // Filtre date début
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        // Filtre date fin
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Tri par date décroissante
        $query->latest();

        return $query->paginate($perPage);
    }

    /**
     * Mise à jour du statut d'un contact
     * 
     * @param Contact $contact
     * @param string $status Nouveau statut (pending, processed, archived)
     * @param User $user Utilisateur qui effectue l'action
     * @param string|null $note Note optionnelle
     * @return bool
     */
    public function updateStatus(Contact $contact, string $status, User $user, ?string $note = null): bool
    {
        $oldStatus = $contact->status;

        DB::beginTransaction();

        try {
            // Mise à jour du statut
            $contact->status = $status;
            
            if ($note) {
                $contact->notes = ($contact->notes ? $contact->notes . "\n\n" : '') . $note;
            }
            
            $contact->save();

            // Log de l'action
            Log::info('Statut contact mis à jour', [
                'contact_id' => $contact->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur mise à jour statut contact', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Suppression d'un contact
     * 
     * @param Contact $contact
     * @param User $user Utilisateur qui effectue l'action
     * @return bool
     */
    public function deleteContact(Contact $contact, User $user): bool
    {
        try {
            // Log avant suppression (pour audit)
            Log::warning('Suppression contact', [
                'contact_id' => $contact->id,
                'contact_email' => $contact->email,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            $contact->delete();

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur suppression contact', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Suppression en masse de contacts
     * 
     * Sécurité:
     * - Transaction DB pour atomicité
     * - Log de chaque suppression
     * - Validation des IDs
     * 
     * @param array $ids IDs des contacts à supprimer
     * @param User $user Utilisateur qui effectue l'action
     * @return int Nombre de contacts supprimés
     */
    public function bulkDelete(array $ids, User $user): int
    {
        DB::beginTransaction();

        try {
            // Récupération des contacts à supprimer
            $contacts = Contact::whereIn('id', $ids)->get();

            if ($contacts->isEmpty()) {
                return 0;
            }

            // Log de la suppression en masse
            Log::warning('Suppression en masse de contacts', [
                'count' => $contacts->count(),
                'ids' => $ids,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // Suppression
            $deletedCount = Contact::whereIn('id', $ids)->delete();

            DB::commit();

            return $deletedCount;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur suppression en masse contacts', [
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Récupération d'un contact avec ses relations
     * 
     * @param int $id
     * @return Contact|null
     */
    public function getContactWithRelations(int $id): ?Contact
    {
        return Contact::find($id);
    }

    /**
     * Statistiques contacts
     * 
     * @return array
     */
    public function getContactStats(): array
    {
        return [
            'total' => Contact::count(),
            'pending' => Contact::where('status', 'pending')->count(),
            'processed' => Contact::where('status', 'processed')->count(),
            'archived' => Contact::where('status', 'archived')->count(),
            'this_month' => Contact::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'today' => Contact::whereDate('created_at', today())->count(),
        ];
    }
}   