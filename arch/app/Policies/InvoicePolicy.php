<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Admin bisa lihat semua.
     * Coach hanya bisa lihat invoice miliknya sendiri.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('coach')) {
            return $user->coach?->id === $invoice->coach_id;
        }

        return false;
    }

    /**
     * Hanya admin yang bisa tandai lunas atau batalkan.
     */
    public function manage(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
