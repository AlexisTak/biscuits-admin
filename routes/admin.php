<?php
// routes/admin.php

use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DevisController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin', 'throttle:120,1'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::prefix('contacts')->name('contacts.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])->name('index');
    Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
    Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
    Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
    Route::patch('/{contact}/status', [ContactController::class, 'updateStatus'])->name('update-status');
    Route::post('/{contact}/note', [ContactController::class, 'addNote'])->name('add-note');
    Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-delete', [ContactController::class, 'bulkDelete'])->name('bulk-delete');

    });

    Route::prefix('tickets')->name('tickets.')->group(function () {
        
        // Liste des tickets
        Route::get('/', [AdminTicketController::class, 'index'])
            ->name('index');
        
        // Voir un ticket
        Route::get('/{ticket}', [AdminTicketController::class, 'show'])
            ->name('show');
        
        // Répondre à un ticket
        Route::post('/{ticket}/reply', [AdminTicketController::class, 'reply'])
            ->name('reply');
        
        // Assigner un ticket
        Route::post('/{ticket}/assign', [AdminTicketController::class, 'assign'])
            ->name('assign');
        
        // Mettre à jour le statut
        Route::post('/{ticket}/status', [AdminTicketController::class, 'updateStatus'])
            ->name('updateStatus');
        
        // Mettre à jour la priorité
        Route::post('/{ticket}/priority', [AdminTicketController::class, 'updatePriority'])
            ->name('updatePriority');
        
        // Télécharger une pièce jointe
        Route::get('/attachment/{attachment}/download', [AdminTicketController::class, 'downloadAttachment'])
            ->name('attachment.download');
        
        // Supprimer un ticket
        Route::delete('/{ticket}', [AdminTicketController::class, 'destroy'])
            ->name('destroy');
        
        // Statistiques (optionnel)
        Route::get('/stats/dashboard', [AdminTicketController::class, 'stats'])
            ->name('stats');
    });
    
    // Devis
    Route::prefix('devis')->name('devis.')->group(function () {
        Route::get('/', [DevisController::class, 'index'])->name('index');
        Route::get('/{devis}', [DevisController::class, 'show'])->name('show');
        Route::patch('/{devis}/status', [DevisController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{devis}/amount', [DevisController::class, 'updateAmount'])->name('update-amount'); // ← LIGNE AJOUTÉE
        Route::delete('/{devis}', [DevisController::class, 'destroy'])->name('destroy');
        Route::get('/{devis}/pdf', [DevisController::class, 'generatePdf'])->name('pdf');
    });
    
    // Utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
    
    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/update', [SettingsController::class, 'update'])->name('update');
    });
    
    // Logs d'activité
    Route::get('/activity-logs', [DashboardController::class, 'activityLogs'])->name('activity-logs');
});