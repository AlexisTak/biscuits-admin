<?php

namespace App\Policies;

use App\Models\DevisRequest;
use App\Models\User;

class DevisRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, DevisRequest $devis): bool
    {
        return $user->isAdmin() 
            || $user->isManager() 
            || $devis->assigned_to === $user->id;
    }

    public function update(User $user, DevisRequest $devis): bool
    {
        return $user->isAdmin() 
            || $user->isManager() 
            || $devis->assigned_to === $user->id;
    }

    public function delete(User $user, DevisRequest $devis): bool
    {
        return $user->isAdmin();
    }

    public function respond(User $user, DevisRequest $devis): bool
    {
        return $user->isAdmin() 
            || $user->isManager() 
            || $devis->assigned_to === $user->id;
    }
}