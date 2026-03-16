<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * EventPolicy
 *
 * Mengatur hak akses untuk operasi pada model Event.
 *
 * Artisan command:
 *   php artisan make:policy EventPolicy --model=Event
 *
 * Aturan akses:
 * ─────────────────────────────────────────────────────────────────
 * ADMIN   → full access: CRUD event, manage peserta, generate cert
 * COACH   → view events, daftar atlet ke event
 * ATHLETE → hanya view event yang published
 * JUDGE   → view events untuk keperluan scoring
 * ─────────────────────────────────────────────────────────────────
 */
class EventPolicy
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
    // viewAny – Melihat daftar event
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua role yang login bisa melihat daftar event.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['coach', 'athlete', 'judge']);
    }

    // ══════════════════════════════════════════════════════════════
    // view – Melihat detail event
    // ══════════════════════════════════════════════════════════════

    /**
     * Semua role bisa lihat detail event.
     * Athlete hanya bisa lihat event yang berstatus 'published'.
     */
    public function view(User $user, Event $event): bool
    {
        if ($user->hasAnyRole(['coach', 'judge'])) {
            return true;
        }

        if ($user->hasRole('athlete')) {
            // Athlete hanya bisa lihat event yang sudah published/ongoing/completed
            return in_array($event->status, ['published', 'ongoing', 'completed']);
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // create – Membuat event baru
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang bisa membuat event.
     */
    public function create(User $user): bool
    {
        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // update – Mengubah event
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang bisa mengubah event.
     */
    public function update(User $user, Event $event): bool
    {
        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // delete – Menghapus event
    // ══════════════════════════════════════════════════════════════

    public function delete(User $user, Event $event): bool
    {
        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // registerParticipant – Mendaftarkan atlet ke event
    // ══════════════════════════════════════════════════════════════

    /**
     * Custom ability: coach mendaftarkan atlet ke event.
     * Event harus masih dalam periode registrasi.
     */
    public function registerParticipant(User $user, Event $event): bool
    {
        return $user->hasRole('coach')
            && $event->isRegistrationOpen();
    }

    // ══════════════════════════════════════════════════════════════
    // generateCertificate – Generate sertifikat pemenang
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang bisa generate sertifikat.
     * Event harus sudah berstatus 'completed'.
     */
    public function generateCertificate(User $user, Event $event): bool
    {
        return false; // hanya admin (bypass via before())
    }
}
