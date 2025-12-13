<?php
// app/Http/Controllers/Admin/UserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller Users Admin
 * 
 * Gestion des utilisateurs administrateurs
 * - Listing
 * - Création
 * - Modification
 * - Suppression
 * - Changement de rôle
 */
class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Liste des utilisateurs
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->get('search'),
            'role' => $request->get('role'),
        ];
        
        $users = $this->userService->getFilteredUsers($filters, 20);
        
        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters
        ]);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        $user = $this->userService->createUser($validated, auth()->user());
        
        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Détail d'un utilisateur
     */
    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user
        ]);
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user
        ]);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        
        $this->userService->updateUser($user, $validated, auth()->user());
        
        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès');
    }

    /**
     * Suppression d'un utilisateur
     * 
     * Sécurité: Impossible de se supprimer soi-même
     */
    public function destroy(User $user): RedirectResponse
    {
        // Protection: ne pas se supprimer soi-même
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }
        
        $this->userService->deleteUser($user, auth()->user());
        
        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }
}