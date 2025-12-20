<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AdminAiController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\AdminTicketController;

// ============================================
// ROUTES AUTH ADMIN
// ============================================

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

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

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::prefix('ai')->name('ai.')->group(function () {
        
        // Dashboard
        Route::get('/', [AdminAiController::class, 'index'])->name('index');
        
        // Conversations
        Route::get('/conversations', [AdminAiController::class, 'conversations'])->name('conversations');
        Route::get('/conversations/{conversation}', [AdminAiController::class, 'show'])->name('show');
        Route::delete('/conversations/{conversation}', [AdminAiController::class, 'destroy'])->name('destroy');
        Route::delete('/conversations', [AdminAiController::class, 'bulkDestroy'])->name('bulk-destroy');
        
        // Stats
        Route::get('/stats', [AdminAiController::class, 'stats'])->name('stats');
        
        // Export
        Route::get('/export', [AdminAiController::class, 'export'])->name('export');
    });
    
});

Route::get('/admin/login', function () {
    if (auth()->check() && auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return view('admin.auth.login');
})->name('admin.login')->middleware('guest');

Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            Auth::logout();
            return back()->withErrors(['email' => 'Accès refusé. Vous devez être administrateur.']);
        }
        
        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    return back()->withErrors(['email' => 'Identifiants incorrects.']);
    
})->name('admin.login.submit')->middleware(['guest', 'throttle:5,1']);

Route::post('/admin/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('admin.logout')->middleware('auth');

Route::middleware(['auth'])->prefix('tickets')->name('tickets.')->group(function () {
    
    // Liste des tickets
    Route::get('/', [TicketController::class, 'index'])->name('index');
    
    // Créer un ticket
    Route::get('/create', [TicketController::class, 'create'])->name('create');
    Route::post('/', [TicketController::class, 'store'])->name('store');
    
    // Voir un ticket
    Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
    
    // Répondre à un ticket
    Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
    
    // Actions
    Route::post('/{ticket}/close', [TicketController::class, 'close'])->name('close');
    Route::post('/{ticket}/reopen', [TicketController::class, 'reopen'])->name('reopen');
    
    // Télécharger pièce jointe
    Route::get('/attachment/{attachment}/download', [TicketController::class, 'downloadAttachment'])
        ->name('attachment.download');
});

Route::middleware(['auth', 'admin'])->prefix('admin/tickets')->name('admin.tickets.')->group(function () {
    
    // Dashboard
    Route::get('/', [AdminTicketController::class, 'index'])->name('index');
    
    // Voir un ticket
    Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
    
    // Répondre
    Route::post('/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('reply');
    
    // Actions admin
    Route::post('/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('assign');
    Route::post('/{ticket}/status', [AdminTicketController::class, 'updateStatus'])->name('update-status');
    Route::post('/{ticket}/priority', [AdminTicketController::class, 'updatePriority'])->name('update-priority');
    
    // Supprimer
    Route::delete('/{ticket}', [AdminTicketController::class, 'destroy'])->name('destroy');
    
    // Statistiques
    Route::get('/statistics/overview', [AdminTicketController::class, 'stats'])->name('stats');
});