<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Match;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * MatchPolicy
 *
 * Mengatur hak akses untuk operasi pada model Match (Pertandingan).
 *
 * Artisan command:
 *   php artisan make:policy MatchPolicy --model=Match
 *
 * Aturan akses:
 * ─────────────────────────────────────────────────────────────────
 * ADMIN   → full access (bypass via before())
 * COACH   → hanya bisa melihat jadwal/match
 * JUDGE   → lihat match + input/update score
 * ATHLETE → hanya lihat jadwal dan hasil
 * ─────────────────────────────────────────────────────────────────
 */
class MatchPolicy
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
    // viewAny – Melihat daftar pertandingan / jadwal
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua role bisa melihat daftar jadwal.
     * Coach, athlete, judge: boleh melihat jadwal pertandingan.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['coach', 'athlete', 'judge']);
    }

    // ══════════════════════════════════════════════════════════════
    // view – Melihat detail satu pertandingan
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua role boleh melihat detail pertandingan.
     *
     * Athlete: hanya boleh melihat pertandingan yang melibatkan dirinya.
     * Coach: hanya pertandingan atlet yang dia kelola.
     * Judge: semua pertandingan.
     */
    public function view(User $user, Match $match): bool
    {
        // Judge bisa lihat semua pertandingan
        if ($user->hasRole('judge')) {
            return true;
        }

        // Coach bisa lihat pertandingan di mana salah satu atletnya terlibat
        if ($user->hasRole('coach')) {
            $athleteIds = $user->athletes()->pluck('id');
            return $athleteIds->contains($match->athlete1_id)
                || $athleteIds->contains($match->athlete2_id);
        }

        // Athlete hanya bisa lihat pertandingannya sendiri
        if ($user->hasRole('athlete')) {
            $athleteProfile = $user->athletes()->first(); // via user_id
            if (! $athleteProfile) return false;

            return $match->athlete1_id === $athleteProfile->id
                || $match->athlete2_id === $athleteProfile->id;
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // create – Membuat jadwal pertandingan baru
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang bisa membuat jadwal.
     * (Admin sudah bypass via before())
     */
    public function create(User $user): bool
    {
        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // update – Mengubah data pertandingan
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang bisa mengubah jadwal pertandingan.
     * Coach, athlete, judge tidak bisa mengatur jadwal.
     */
    public function update(User $user, Match $match): bool
    {
        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // delete – Menghapus pertandingan
    // ══════════════════════════════════════════════════════════════

    public function delete(User $user, Match $match): bool
    {
        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // inputScore – Input nilai untuk pertandingan ini
    // ══════════════════════════════════════════════════════════════

    /**
     * Custom ability: apakah user boleh menginput nilai?
     * Hanya judge dan admin.
     */
    public function inputScore(User $user, Match $match): bool
    {
        return $user->hasRole('judge')
            && $match->status === 'ongoing'; // hanya bisa input jika match sedang berlangsung
    }

    // ══════════════════════════════════════════════════════════════
    // viewResult – Melihat hasil pertandingan
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua role bisa melihat hasil pertandingan.
     */
    public function viewResult(User $user, Match $match): bool
    {
        return $user->hasAnyRole(['coach', 'athlete', 'judge']);
    }
}
