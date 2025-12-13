<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isManager();
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->isAdmin() 
            || $user->isManager() 
            || $contact->assigned_to === $user->id;
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->isAdmin() 
            || $user->isManager() 
            || $contact->assigned_to === $user->id;
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->isAdmin();
    }

    public function respond(User $user, Contact $contact): bool
    {
        return $user->isAdmin() 
            || $user->isManager() 
            || $contact->assigned_to === $user->id;
    }
}