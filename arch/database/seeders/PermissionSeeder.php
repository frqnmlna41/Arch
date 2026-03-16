<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * PermissionSeeder
 *
 * Membuat semua permission dan menghubungkannya ke role.
 *
 * Artisan command:
 *   php artisan make:seeder PermissionSeeder
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=PermissionSeeder
 */
class PermissionSeeder extends Seeder
{
    /**
     * Daftar lengkap semua permission dalam sistem.
     *
     * Konvensi penamaan: "<action> <resource>"
     * Sesuai dengan method authorize() di Controller & Policy.
     */
    private array $permissions = [

        // ── User & Role Management (admin only) ───────────────────
        'manage users',
        'manage roles',

        // ── Sport & Discipline Management ─────────────────────────
        'manage sports',
        'manage disciplines',

        // ── Arena Management ──────────────────────────────────────
        'manage arenas',

        // ── Event Management ──────────────────────────────────────
        'manage events',
        'view events',

        // ── Athlete Management ────────────────────────────────────
        'manage athletes',   // admin: full CRUD
        'create athletes',   // coach: tambah atlet baru
        'update athletes',   // coach: edit atlet miliknya
        'view athletes',     // coach, judge: lihat daftar atlet

        // ── Participant Management ─────────────────────────────────
        'manage participants',
        'register participant', // coach: daftarkan atlet ke event

        // ── Match Management ──────────────────────────────────────
        'manage matches',
        'view matches',

        // ── Schedule ─────────────────────────────────────────────
        'view schedule',

        // ── Scoring ───────────────────────────────────────────────
        'input score',
        'update score',
        'manage scoring',    // admin: kelola semua nilai

        // ── Results & Winners ─────────────────────────────────────
        'manage winners',
        'view results',

        // ── Certificate ───────────────────────────────────────────
        'generate certificates',
        'view certificates',
    ];

    /**
     * Mapping permission ke setiap role.
     * Admin di-handle terpisah (mendapat semua permission).
     */
    private array $rolePermissions = [

        'coach' => [
            'create athletes',
            'update athletes',
            'view athletes',
            'register participant',
            'view participants',  // tidak ada di list utama, tambah jika perlu
            'view events',
            'view schedule',
            'view matches',
            'view results',
            'view certificates',
        ],

        'athlete' => [
            'view schedule',
            'view matches',
            'view results',
            'view certificates',
        ],

        'judge' => [
            'view schedule',
            'view matches',
            'view athletes',
            'input score',
            'update score',
            'view results',
        ],
    ];

    public function run(): void
    {
        // Reset cache permission Spatie agar perubahan langsung efektif
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('  Creating permissions...');

        // ── Buat semua permission ────────────────────────────────
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate([
                'name'       => $name,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info('  ✅ ' . count($this->permissions) . ' permissions created.');

        // ── Assign permission ke role ────────────────────────────

        // ADMIN → semua permission tanpa terkecuali
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());
        $this->command->info('  ✅ Role [admin] → ALL permissions assigned.');

        // COACH, ATHLETE, JUDGE → permission spesifik
        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Filter hanya permission yang ada di database
            $validPermissions = collect($permissions)
                ->filter(fn ($p) => Permission::where('name', $p)->exists())
                ->values()
                ->toArray();

            $role->syncPermissions($validPermissions);

            $this->command->info(
                "  ✅ Role [{$roleName}] → " . count($validPermissions) . ' permissions assigned.'
            );
        }
    }
}
