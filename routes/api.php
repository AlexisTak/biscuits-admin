<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DevisController;
use App\Http\Middleware\VerifyApiOrigin;
use App\Http\Controllers\AiController;
use App\Http\Controllers\TicketController;

/*
|--------------------------------------------------------------------------
| Routes API Publiques
|--------------------------------------------------------------------------
*/

// Health check (pas de throttle, pour monitoring)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Biscuits Dev API',
    ]);
});

// Formulaires publics (depuis frontend Astro)
Route::middleware(['throttle:contact'])->group(function () {
    // Contact
    Route::post('/contacts', [ContactController::class, 'store']);
    
    // Devis
    Route::post('/devis', [DevisController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Routes API Tickets (protégées)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('tickets')->group(function () {
    // AI Assistants
    Route::post('/{assistant}', [AiController::class, 'handle'])
        ->whereIn('assistant', ['support', 'dev', 'sales']);
    
    // CRUD Tickets
    Route::get('/', [TicketController::class, 'index']);
    Route::post('/', [TicketController::class, 'store']);
    Route::get('/{ticket}', [TicketController::class, 'show']);
    Route::post('/{ticket}/reply', [TicketController::class, 'reply']);
});

/*
|--------------------------------------------------------------------------
| Routes API Admin (protégées)
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'service' => 'Biscuits Dev API',
    ]);
});

// Formulaire de contact (PUBLIC - depuis Astro)
Route::middleware(['throttle:contact'])->group(function () {
    Route::post('/contacts', [ContactController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('admin')->group(function () {
    
    // AI Assistants Admin
    Route::post('/{assistant}', [AiController::class, 'handle'])
        ->whereIn('assistant', ['support', 'dev', 'sales']);

    // Conversations IA
    Route::get('/conversations', [AiController::class, 'index']);
    Route::get('/conversations/{conversation}', [AiController::class, 'show']);
    Route::delete('/conversations/{conversation}', [AiController::class, 'destroy']);

    // CONTACTS ADMIN
    Route::prefix('contacts')->group(function () {
        Route::get('/', [ContactController::class, 'index']);
        Route::get('/{contact}', [ContactController::class, 'show']);
        Route::patch('/{contact}', [ContactController::class, 'update']);
        Route::delete('/{contact}', [ContactController::class, 'destroy']);
        
        // Actions spécifiques
        Route::post('/{contact}/mark-read', [ContactController::class, 'markAsRead']);
        Route::post('/{contact}/archive', [ContactController::class, 'archive']);
        Route::post('/{contact}/spam', [ContactController::class, 'markAsSpam']);
        Route::post('/{contact}/assign', [ContactController::class, 'assign']);
        Route::post('/{contact}/responses', [ContactController::class, 'addResponse']);
    });
    
    // DEVIS ADMIN
    Route::prefix('devis')->group(function () {
        Route::get('/', [DevisController::class, 'index']);
        Route::get('/{devis}', [DevisController::class, 'show']);
        Route::delete('/{devis}', [DevisController::class, 'destroy']);
        
        // Actions spécifiques
        Route::post('/{devis}/quote', [DevisController::class, 'sendQuote']);
        Route::post('/{devis}/archive', [DevisController::class, 'archive']);
        Route::post('/{devis}/responses', [DevisController::class, 'addResponse']);
    });
});