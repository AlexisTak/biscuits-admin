<?php
// app/Http/Controllers/Admin/SettingsController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

/**
 * Controller Settings Admin
 * 
 * Gestion des paramètres de l'application
 * - Paramètres généraux
 * - Cache
 * - Maintenance
 */
class SettingsController extends Controller
{
    /**
     * Page des paramètres
     */
    public function index(): View
    {
        // Statistiques système
        $stats = [
            'cache_size' => $this->getCacheSize(),
            'logs_size' => $this->getLogsSize(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];

        return view('admin.settings.index', [
            'stats' => $stats
        ]);
    }

    /**
     * Mise à jour des paramètres
     */
    public function update(Request $request): RedirectResponse
    {
        $action = $request->get('action');

        switch ($action) {
            case 'clear_cache':
                $this->clearCache();
                return redirect()
                    ->route('admin.settings.index')
                    ->with('success', 'Cache vidé avec succès');

            case 'clear_logs':
                $this->clearLogs();
                return redirect()
                    ->route('admin.settings.index')
                    ->with('success', 'Logs supprimés avec succès');

            case 'optimize':
                $this->optimize();
                return redirect()
                    ->route('admin.settings.index')
                    ->with('success', 'Application optimisée avec succès');

            default:
                return redirect()
                    ->route('admin.settings.index')
                    ->with('error', 'Action inconnue');
        }
    }

    /**
     * Vider le cache
     */
    private function clearCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
    }

    /**
     * Supprimer les anciens logs
     */
    private function clearLogs(): void
    {
        $logPath = storage_path('logs');
        $files = glob($logPath . '/*.log');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * Optimiser l'application
     */
    private function optimize(): void
    {
        Artisan::call('optimize');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
    }

    /**
     * Récupérer la taille du cache
     */
    private function getCacheSize(): string
    {
        $cachePath = storage_path('framework/cache');
        $size = 0;

        if (is_dir($cachePath)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cachePath)) as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }

        return $this->formatBytes($size);
    }

    /**
     * Récupérer la taille des logs
     */
    private function getLogsSize(): string
    {
        $logPath = storage_path('logs');
        $size = 0;

        if (is_dir($logPath)) {
            foreach (glob($logPath . '/*.log') as $file) {
                if (is_file($file)) {
                    $size += filesize($file);
                }
            }
        }

        return $this->formatBytes($size);
    }

    /**
     * Formater les bytes en format lisible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}