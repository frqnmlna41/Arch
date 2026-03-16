<?php

namespace App\Policies;

use App\Models\Score;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ScorePolicy
 *
 * Mengatur hak akses untuk operasi pada model Score (Nilai).
 *
 * Artisan command:
 *   php artisan make:policy ScorePolicy --model=Score
 *
 * Aturan akses:
 * ─────────────────────────────────────────────────────────────────
 * ADMIN   → full access (bypass via before())
 * JUDGE   → bisa input & update nilai miliknya sendiri
 * COACH   → hanya lihat nilai atlet yang dikelolanya
 * ATHLETE → hanya lihat nilai miliknya sendiri
 * ─────────────────────────────────────────────────────────────────
 */
class ScorePolicy
{
    use HandlesAuthorization;

    /**
     * Admin bypass semua check.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    // ══════════════════════════════════════════════════════════════
    // viewAny – Melihat daftar nilai
    // ══════════════════════════════════════════════════════════════

    /**
     * Coach dan judge bisa melihat daftar nilai.
     * Athlete tidak bisa melihat nilai orang lain.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['coach', 'judge']);
    }

    // ══════════════════════════════════════════════════════════════
    // view – Melihat detail satu nilai
    // ══════════════════════════════════════════════════════════════

    /**
     * Judge: hanya nilai yang dia input sendiri.
     * Coach: hanya nilai untuk atlet yang dia kelola.
     * Athlete: hanya nilai miliknya sendiri.
     */
    public function view(User $user, Score $score): bool
    {
        if ($user->hasRole('judge')) {
            return $score->judge_id === $user->id;
        }

        if ($user->hasRole('coach')) {
            $athleteIds = $user->athletes()->pluck('id');
            return $athleteIds->contains($score->athlete_id);
        }

        if ($user->hasRole('athlete')) {
            $athleteProfile = $user->athletes()->first();
            return $athleteProfile && $score->athlete_id === $athleteProfile->id;
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // create – Menambah nilai baru
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya judge yang bisa menginput nilai.
     * Pertandingan harus dalam status 'ongoing'.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('judge');
    }

    // ══════════════════════════════════════════════════════════════
    // update – Mengubah nilai
    // ══════════════════════════════════════════════════════════════

    /**
     * Judge hanya bisa mengubah nilai yang DIA sendiri input.
     * Tidak bisa mengubah nilai judge lain.
     */
    public function update(User $user, Score $score): bool
    {
        if ($user->hasRole('judge')) {
            return $score->judge_id === $user->id;
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // delete – Menghapus nilai
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang bisa menghapus nilai.
     * (Admin sudah bypass via before())
     */
    public function delete(User $user, Score $score): bool
    {
        return false;
    }
}
