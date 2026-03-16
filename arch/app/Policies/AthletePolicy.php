<?php

namespace App\Policies;

use App\Models\Athlete;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * AthletePolicy
 *
 * Mengatur hak akses untuk operasi CRUD pada model Athlete.
 *
 * Artisan command:
 *   php artisan make:policy AthletePolicy --model=Athlete
 *
 * Aturan akses:
 * ─────────────────────────────────────────────────────────────────
 * ADMIN
 *   viewAny  → ✅  Lihat semua atlet
 *   view     → ✅  Lihat detail atlet manapun
 *   create   → ✅  Tambah atlet baru
 *   update   → ✅  Edit atlet manapun
 *   delete   → ✅  Hapus atlet manapun
 *   restore  → ✅  Pulihkan atlet yang di-soft delete
 *   forceDelete → ✅  Hapus permanen
 *
 * COACH
 *   viewAny  → ✅  Lihat atlet miliknya sendiri
 *   view     → ✅  Lihat detail atlet miliknya
 *   create   → ✅  Tambah atlet baru (coach_id = dirinya)
 *   update   → ✅  Edit atlet miliknya saja
 *   delete   → ❌  Tidak bisa hapus atlet
 *   restore  → ❌
 *   forceDelete → ❌
 *
 * ATHLETE
 *   viewAny  → ❌  Tidak bisa lihat daftar semua atlet
 *   view     → ✅  Hanya bisa lihat profil DIRINYA SENDIRI
 *   create   → ❌
 *   update   → ❌
 *   delete   → ❌
 *
 * JUDGE
 *   viewAny  → ✅  Lihat daftar atlet (untuk input score)
 *   view     → ✅  Lihat detail atlet
 *   create   → ❌
 *   update   → ❌
 *   delete   → ❌
 * ─────────────────────────────────────────────────────────────────
 *
 * Cara mendaftarkan policy (di AuthServiceProvider atau auto-discovery):
 *
 * protected $policies = [
 *     Athlete::class => AthletePolicy::class,
 * ];
 */
class AthletePolicy
{
    use HandlesAuthorization;

    /**
     * Bypass semua gate check untuk super admin.
     * Method ini dipanggil sebelum method policy lainnya.
     * Return null = lanjut ke method policy berikutnya (tidak bypass).
     */
    public function before(User $user, string $ability): bool|null
    {
        // Admin bypass SEMUA policy check
        if ($user->hasRole('admin')) {
            return true;
        }

        return null; // lanjut ke method policy masing-masing
    }

    // ══════════════════════════════════════════════════════════════
    // viewAny – Melihat daftar semua atlet
    // ══════════════════════════════════════════════════════════════

    /**
     * Tentukan apakah user dapat melihat daftar atlet.
     *
     * Coach     → hanya melihat atlet miliknya (filter di controller)
     * Judge     → bisa melihat semua (untuk keperluan scoring)
     * Athlete   → tidak bisa lihat daftar semua atlet
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['coach', 'judge']);
    }

    // ══════════════════════════════════════════════════════════════
    // view – Melihat detail satu atlet
    // ══════════════════════════════════════════════════════════════

    /**
     * Tentukan apakah user dapat melihat detail atlet tertentu.
     *
     * Coach   → hanya atlet yang dia kelola (coach_id == user->id)
     * Athlete → hanya profil dirinya sendiri (via user_id)
     * Judge   → bisa lihat atlet manapun
     */
    public function view(User $user, Athlete $athlete): bool
    {
        // Judge: boleh lihat atlet siapapun
        if ($user->hasRole('judge')) {
            return true;
        }

        // Coach: hanya atlet miliknya
        if ($user->hasRole('coach')) {
            return $athlete->coach_id === $user->id;
        }

        // Athlete: hanya diri sendiri
        if ($user->hasRole('athlete')) {
            return $athlete->user_id === $user->id;
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // create – Menambah atlet baru
    // ══════════════════════════════════════════════════════════════

    /**
     * Tentukan apakah user dapat menambah atlet baru.
     * Hanya coach yang boleh menambah (admin sudah bypass di before()).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('coach');
    }

    // ══════════════════════════════════════════════════════════════
    // update – Mengubah data atlet
    // ══════════════════════════════════════════════════════════════

    /**
     * Tentukan apakah user dapat mengubah data atlet.
     *
     * Coach → hanya bisa edit atlet yang dia kelola.
     * Athlete/Judge → tidak bisa edit siapapun.
     */
    public function update(User $user, Athlete $athlete): bool
    {
        if ($user->hasRole('coach')) {
            return $athlete->coach_id === $user->id;
        }

        return false;
    }

    // ══════════════════════════════════════════════════════════════
    // delete – Menghapus atlet (soft delete)
    // ══════════════════════════════════════════════════════════════

    /**
     * Hanya admin yang boleh menghapus atlet.
     * Coach, athlete, dan judge tidak bisa.
     * (Admin sudah ditangani oleh before())
     */
    public function delete(User $user, Athlete $athlete): bool
    {
        return false; // non-admin tidak bisa hapus
    }

    // ══════════════════════════════════════════════════════════════
    // restore – Memulihkan soft-deleted atlet
    // ══════════════════════════════════════════════════════════════

    public function restore(User $user, Athlete $athlete): bool
    {
        return false; // hanya admin (sudah bypass di before())
    }

    // ══════════════════════════════════════════════════════════════
    // forceDelete – Hapus permanen
    // ══════════════════════════════════════════════════════════════

    public function forceDelete(User $user, Athlete $athlete): bool
    {
        return false; // hanya admin
    }
}
