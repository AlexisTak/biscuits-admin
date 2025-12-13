<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Admin - Vérifie que l'utilisateur a le rôle admin
 * 
 * Sécurité:
 * - Vérifie l'authentification
 * - Vérifie le rôle admin
 * - Log toutes les tentatives d'accès
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {

        
        // Vérification auth
        if (!$request->user()) {
            Log::warning('Tentative d\'accès admin sans authentification', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        // Vérification rôle admin
        if (!$request->user()->isAdmin()) {
            Log::warning('Tentative d\'accès admin non autorisée', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Droits administrateur requis.'
            ], 403);
        }

        // Log accès admin (pour audit)
        Log::info('Accès admin', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'action' => $request->method() . ' ' . $request->path(),
            'ip' => $request->ip()
        ]);

        return $next($request);
    }
    
}