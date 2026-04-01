<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * RolePermissionSeeder
 *
 * Membuat semua role dan permission untuk sistem CMS Tournament Management.
 *
 * Artisan command:
 *   php artisan make:seeder RolePermissionSeeder
 *
 * Role yang dibuat:
 *   - admin   → semua permission
 *   - coach   → create athletes, view schedule
 *   - athlete → view schedule
 *   - judge   → input score, view schedule
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar semua permission dalam sistem.
     * Format: 'resource.action'
     */
    private array $permissions = [
        // ── Event Management ──────────────────────────────
        'manage events',        // admin: CRUD event penuh
        'view events',          // semua role

        // ── Athlete Management ────────────────────────────
        'manage athletes',      // admin: CRUD atlet penuh
        'create athletes',      // coach: tambah atlet
        'view athletes',        // coach, judge

        // ── Match Management ──────────────────────────────
        'manage matches',       // admin: CRUD pertandingan
        'view schedule',        // semua role: lihat jadwal

        // ── Scoring ───────────────────────────────────────
        'input score',          // judge: input nilai
        'manage scores',        // admin: kelola semua nilai
        'view scores',          // semua role

        // ── Sport & Discipline ────────────────────────────
        'manage sports',        // admin
        'manage disciplines',   // admin

        // ── Category ─────────────────────────────────────
        'manage age categories',    // admin
        'manage event categories',  // admin

        // ── Arena ─────────────────────────────────────────
        'manage arenas',        // admin

        // ── Participants ───────────────────────────────────
        'manage participants',  // admin
        'register participant', // coach: daftarkan atlet ke event
        'view participants',    // coach, judge

        // ── Results & Winners ─────────────────────────────
        'manage results',       // admin
        'view results',         // semua role

        // ── Certificate ───────────────────────────────────
        'generate certificate', // admin
        'view certificate',     // semua role (lihat sertifikat)

        // ── User & Role Management ────────────────────────
        'manage users',         // admin
        'manage roles',         // admin

        // ── Perguruan Management ──────────────────────────
        'manage perguruans',    // admin
        'verify perguruan',     // admin
        'register perguruan',   // perguruan only
    ];

    /**
     * Permission mapping per role.
     */
    private array $rolePermissions = [
        'coach' => [
            'create athletes',
            'view athletes',
            'view events',
            'view schedule',
            'register participant',
            'view participants',
            'view scores',
            'view results',
            'view certificate',
        ],
        'perguruan' => [  // NEW ROLE
            'register perguruan',
            'create athletes',
            'view athletes',
            'view events',
            'view schedule',
            'register participant',
            'view participants',
            'view scores',
            'view results',
            'view certificate',
        ],
        'athlete' => [
            'view schedule',
            'view events',
            'view results',
            'view certificate',
        ],
        'judge' => [
            'input score',
            'view schedule',
            'view events',
            'view athletes',
            'view scores',
            'view participants',
            'view results',
        ],
    ];

    public function run(): void
    {
        // Reset cache permission Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('  Creating permissions...');

        // Buat semua permission
        foreach ($this->permissions as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('  ✅ ' . count($this->permissions) . ' permissions created.');

        // ── Buat Role: admin ──────────────────────────────
        $adminRole = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => 'web',
        ]);
        // Admin mendapatkan SEMUA permission
        $adminRole->syncPermissions(Permission::all());
        $this->command->info('  ✅ Role [admin] created → all permissions assigned.');

        // ── Buat Role: coach, athlete, judge, perguruan ──
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);
            $this->command->info("  ✅ Role [{$roleName}] created → " . count($permissions) . ' permissions assigned.');
        }
    }
}
