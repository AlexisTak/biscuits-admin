<?php
// app/Services/Admin/DevisService.php

namespace App\Services\Admin;

use App\Models\Devis;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Service Devis Admin
 * 
 * Responsabilité: Logique métier pour la gestion des devis
 */
class DevisService
{
    /**
     * Récupération des devis avec filtres et pagination
     */
    public function getFilteredDevis(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Devis::with('contact');

        // Filtre recherche (email du contact)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('contact', function($q) use ($search) {
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
     * Mise à jour du statut d'un devis
     */
    public function updateStatus(Devis $devis, string $status, User $user, ?string $note = null): bool
    {
        $oldStatus = $devis->status;

        DB::beginTransaction();

        try {
            $devis->status = $status;
            
            if ($note) {
                $devis->notes = ($devis->notes ? $devis->notes . "\n\n" : '') . $note;
            }
            
            $devis->save();

            // Log de l'action
            Log::info('Statut devis mis à jour', [
                'devis_id' => $devis->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            // 🔥 INVALIDATION DU CACHE DASHBOARD
            $this->clearDashboardCache();

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Erreur mise à jour statut devis', [
                'devis_id' => $devis->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Suppression d'un devis
     */
    public function deleteDevis(Devis $devis, User $user): bool
    {
        try {
            Log::warning('Suppression devis', [
                'devis_id' => $devis->id,
                'user_id' => $user->id
            ]);

            $devis->delete();
            
            // 🔥 INVALIDATION DU CACHE DASHBOARD
            $this->clearDashboardCache();
            
            return true;

        } catch (\Exception $e) {
            Log::error('Erreur suppression devis', [
                'devis_id' => $devis->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Statistiques devis
     */
    public function getDevisStats(): array
    {
        return [
            'total' => Devis::count(),
            'pending' => Devis::where('status', 'pending')->count(),
            'approved' => Devis::where('status', 'approved')->count(),
            'rejected' => Devis::where('status', 'rejected')->count(),
            'total_revenue' => Devis::where('status', 'approved')->sum('amount'),
            'this_month' => Devis::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Invalidation du cache du dashboard
     * 
     * 🆕 NOUVELLE MÉTHODE
     */
    private function clearDashboardCache(): void
    {
        Cache::forget('admin.dashboard.stats');
        
        Log::debug('Cache dashboard invalidé après modification devis');
    }
}