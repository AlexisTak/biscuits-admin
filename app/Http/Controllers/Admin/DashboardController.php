<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Dashboard Admin
 * 
 * Responsabilité: Orchestration uniquement
 * Logique métier déléguée au DashboardService
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    /**
     * Affichage du dashboard admin
     * 
     * Stats:
     * - Nombre de contacts
     * - Nombre de devis
     * - Revenus
     * - Activité récente
     */
    public function index(Request $request): View
{
    // Récupération des stats via service
    $stats = $this->dashboardService->getDashboardStats();
    
    // 🔍 DEBUG
    \Log::info('Stats dashboard', $stats);
    
    // Récupération activité récente
    $recentActivity = $this->dashboardService->getRecentActivity(10);
    
    return view('admin.dashboard', [
        'stats' => $stats,
        'recentActivity' => $recentActivity,
        'user' => $request->user()
    ]);
}

    /**
     * Page logs d'activité complète
     */
    public function activityLogs(Request $request): View
    {
        $page = $request->get('page', 1);
        $perPage = 50;
        
        $logs = $this->dashboardService->getActivityLogs($page, $perPage);
        
        return view('admin.activity-logs', [
            'logs' => $logs,
            'user' => $request->user()
        ]);
    }
}
