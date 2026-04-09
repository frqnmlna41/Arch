<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $currentUser, User $user): bool
    {
        // Admin can view all, others only self
        return $currentUser->isAdmin() || $currentUser->id === $user->id;
    }

    /**
     * Determine whether the user can verify coach/perguruan account.
     */
    public function verify(User $currentUser, User $user): bool
    {
        return $currentUser->isAdmin() &&\n               $user->status === 'pending';
    }
}

