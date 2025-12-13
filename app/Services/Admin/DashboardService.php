<?php
// app/Services/Admin/DashboardService.php

namespace App\Services\Admin;

use App\Models\Contact;
use App\Models\Devis;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getDashboardStats(): array
    {
        return Cache::remember('admin.dashboard.stats', 300, function () {
            
            $totalContacts = Contact::count();
            $newContactsThisMonth = Contact::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            $totalDevis = Devis::count();
            $pendingDevis = Devis::where('status', 'pending')->count();
            $approvedDevis = Devis::where('status', 'approved')->count();
            $revenue = Devis::where('status', 'approved')->sum('amount');
            
            $recentContacts = Contact::latest()
                ->limit(5)
                ->get()
                ->map(function ($contact) {
                    return [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'email' => $contact->email,
                        'service' => $contact->service ?? 'N/A',
                        'created_at' => $contact->created_at->diffForHumans(),
                        'status' => $contact->status ?? 'pending'
                    ];
                })
                ->toArray();
            
            $recentDevis = Devis::with('contact')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($devis) {
                    return [
                        'id' => $devis->id,
                        'contact_name' => $devis->contact ? $devis->contact->name : 'Contact supprimé',
                        'service' => $devis->service,
                        'amount' => $devis->amount,
                        'status' => $devis->status,
                        'created_at' => $devis->created_at->diffForHumans()
                    ];
                })
                ->toArray();
            
            return [
                'totalContacts' => $totalContacts,
                'newContactsThisMonth' => $newContactsThisMonth,
                'totalDevis' => $totalDevis,
                'pendingDevis' => $pendingDevis,
                'approvedDevis' => $approvedDevis,
                'revenue' => round($revenue, 2),
                'recentContacts' => $recentContacts,
                'recentDevis' => $recentDevis,
                'monthlyStats' => []
            ];
        });
    }

    public function getRecentActivity(int $limit = 10): array
    {
        return [];
    }

    public function getActivityLogs(int $page = 1, int $perPage = 50): array
    {
        return [
            'data' => [],
            'current_page' => 1,
            'last_page' => 1,
            'total' => 0,
            'per_page' => $perPage
        ];
    }

    public function clearCache(): void
    {
        Cache::forget('admin.dashboard.stats');
    }
}