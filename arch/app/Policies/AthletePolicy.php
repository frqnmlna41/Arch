<?php

namespace App\Policies;

use App\Models\Athlete;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AthletePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'coach', 'judge', 'perguruan']);
    }

    public function view(User $user, Athlete $athlete): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->hasRole(['coach', 'perguruan'])) {
            return $athlete->coach_id === $user->id || $athlete->perguruan_id === $user->perguruan_id;
        }
        return true; // judge/athlete
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create athletes');
    }

    public function update(User $user, Athlete $athlete): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->hasRole(['coach', 'perguruan'])) {
            return $athlete->coach_id === $user->id || $athlete->perguruan_id === $user->perguruan_id;
        }
        return false;
    }

    public function delete(User $user, Athlete $athlete): bool
    {
        return $user->isAdmin();
    }

    public function viewOwn(Athlete $athlete, User $user): bool
    {
        return $athlete->coach_id === $user->id || $athlete->perguruan_id === $user->perguruan_id;
    }
}
