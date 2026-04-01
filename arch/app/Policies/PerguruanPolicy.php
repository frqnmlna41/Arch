<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class PerguruanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function verify(User $user, User $model): bool
    {
        return $user->isAdmin() && $model->hasRole('perguruan') && $model->status === 'pending';
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }
}
