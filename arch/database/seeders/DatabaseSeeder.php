<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DatabaseSeeder
 *
 * Orchestrator utama yang memanggil semua seeder dalam urutan
 * dependency yang benar.
 *
 * Jalankan dengan:
 *   php artisan db:seed
 *   php artisan migrate:fresh --seed
 *
 * ─────────────────────────────────────────────────────────────────
 * URUTAN EKSEKUSI (dependency order):
 * ─────────────────────────────────────────────────────────────────
 *
 *  1. RolePermissionSeeder   → tidak ada dependency
 *  2. UserSeeder             → butuh: roles (dari Spatie)
 *  3. SportSeeder            → tidak ada dependency
 *  4. AgeCategorySeeder      → butuh: sports
 *  5. DisciplineSeeder       → butuh: sports
 *  6. DisciplineAgeCategorySeeder → butuh: disciplines, age_categories
 *  7. ArenaSeeder            → tidak ada dependency
 *  8. EventSeeder            → butuh: users (admin)
 *  9. AthleteSeeder          → butuh: users (coach, athlete)
 *
 * ─────────────────────────────────────────────────────────────────
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║   🏆  Tournament CMS Database Seeder                ║');
        $this->command->info('║   Wushu & Wing Chun Management System               ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');

        DB::transaction(function () {

            // ── 1. RBAC: Roles & Permissions ──────────────────────
            $this->command->info('┌─ [1/9] RolePermissionSeeder');
            $this->call(RolePermissionSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 2. Users ───────────────────────────────────────────
            $this->command->info('┌─ [2/9] UserSeeder');
            $this->call(UserSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 3. Sports ──────────────────────────────────────────
            $this->command->info('┌─ [3/9] SportSeeder');
            $this->call(SportSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 4. Age Categories ──────────────────────────────────
            $this->command->info('┌─ [4/9] AgeCategorySeeder');
            $this->call(AgeCategorySeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 5. Disciplines ─────────────────────────────────────
            $this->command->info('┌─ [5/9] DisciplineSeeder');
            $this->call(DisciplineSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 6. Pivot: Discipline ↔ Age Category ───────────────
            $this->command->info('┌─ [6/9] DisciplineAgeCategorySeeder');
            $this->call(DisciplineAgeCategorySeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 7. Arenas ──────────────────────────────────────────
            $this->command->info('┌─ [7/9] ArenaSeeder');
            $this->call(ArenaSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 8. Events ──────────────────────────────────────────
            $this->command->info('┌─ [8/9] EventSeeder');
            $this->call(EventSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

            // ── 9. Athletes ────────────────────────────────────────
            $this->command->info('┌─ [9/9] AthleteSeeder');
            $this->call(AthleteSeeder::class);
            $this->command->info('└─ ✅ Done');
            $this->command->info('');

        });

        // ── Summary ────────────────────────────────────────────────
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║   ✅  Semua seeder berhasil dijalankan!             ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║   Akun default yang tersedia:                       ║');
        $this->command->info('║   admin@tournament.com   → password  (admin)        ║');
        $this->command->info('║   coach@tournament.com   → password  (coach)        ║');
        $this->command->info('║   judge@tournament.com   → password  (judge)        ║');
        $this->command->info('║   athlete@tournament.com → password  (athlete)      ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
