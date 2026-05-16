<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    /**
     * Admin bisa lihat semua.
     * Coach hanya bisa lihat miliknya sendiri.
     */
    public function view(User $user, Registration $registration): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('coach')) {
            return $user->coach?->id === $registration->coach_id;
        }

        return false;
    }

    /**
     * Hanya coach pemilik registrasi yang bisa hapus,
     * dan hanya jika statusnya masih pending.
     */
    public function delete(User $user, Registration $registration): bool
    {
        return $user->hasRole('coach')
            && $user->coach?->id === $registration->coach_id
            && $registration->status === 'pending';
    }

    /**
     * Hanya admin yang bisa approve/reject.
     */
    public function approve(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
