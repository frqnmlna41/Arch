<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * RoleSeeder
 *
 * Membuat semua role dan menghubungkannya ke permission
 * menggunakan givePermissionTo() sesuai best practice Spatie.
 *
 * Artisan command:
 *   php artisan make:seeder RoleSeeder
 *
 * Jalankan dengan:
 *   php artisan db:seed --class=RoleSeeder
 *
 * PENTING: PermissionSeeder harus dijalankan sebelum RoleSeeder
 * karena permission harus ada lebih dahulu sebelum di-assign ke role.
 *
 * ─────────────────────────────────────────────────────────────────
 * ROLE & AKSES:
 *
 * admin   → semua permission (full system access)
 * coach   → manage atlet sendiri, daftar peserta, lihat jadwal
 * athlete → hanya lihat jadwal & hasil pertandingan
 * judge   → lihat jadwal, input & update nilai
 * ─────────────────────────────────────────────────────────────────
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache sebelum operasi
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ══════════════════════════════════════════════════════════
        // 1. ROLE: admin
        //    Akses penuh ke seluruh sistem
        // ══════════════════════════════════════════════════════════
        $admin = Role::firstOrCreate([
            'name'       => 'admin',
            'guard_name' => 'web',
        ]);

        // Admin mendapatkan SEMUA permission yang ada
        $admin->syncPermissions(Permission::all());

        $this->command->info('  ✅ Role [admin] created → ALL permissions.');

        // ══════════════════════════════════════════════════════════
        // 2. ROLE: coach
        //    Manage atlet sendiri, daftarkan peserta, lihat jadwal
        // ══════════════════════════════════════════════════════════
        $coach = Role::firstOrCreate([
            'name'       => 'coach',
            'guard_name' => 'web',
        ]);

        $coach->syncPermissions([]);  // reset dulu

        $coach->givePermissionTo('create athletes');
        $coach->givePermissionTo('update athletes');
        $coach->givePermissionTo('view athletes');
        $coach->givePermissionTo('register participant');
        $coach->givePermissionTo('view events');
        $coach->givePermissionTo('view schedule');
        $coach->givePermissionTo('view matches');
        $coach->givePermissionTo('view results');
        $coach->givePermissionTo('view certificates');

        $this->command->info('  ✅ Role [coach] created → 9 permissions.');

        // ══════════════════════════════════════════════════════════
        // 3. ROLE: athlete
        //    Hanya melihat jadwal dan hasil pertandingan
        // ══════════════════════════════════════════════════════════
        $athlete = Role::firstOrCreate([
            'name'       => 'athlete',
            'guard_name' => 'web',
        ]);

        $athlete->syncPermissions([]);

        $athlete->givePermissionTo('view schedule');
        $athlete->givePermissionTo('view matches');
        $athlete->givePermissionTo('view results');
        $athlete->givePermissionTo('view certificates');

        $this->command->info('  ✅ Role [athlete] created → 4 permissions.');

        // ══════════════════════════════════════════════════════════
        // 4. ROLE: judge
        //    Lihat jadwal, input dan update nilai
        // ══════════════════════════════════════════════════════════
        $judge = Role::firstOrCreate([
            'name'       => 'judge',
            'guard_name' => 'web',
        ]);

        $judge->syncPermissions([]);

        $judge->givePermissionTo('view schedule');
        $judge->givePermissionTo('view matches');
        $judge->givePermissionTo('view athletes');
        $judge->givePermissionTo('input score');
        $judge->givePermissionTo('update score');
        $judge->givePermissionTo('view results');

        $this->command->info('  ✅ Role [judge] created → 6 permissions.');

        // Reset cache setelah selesai
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->newLine();
        $this->command->table(
            ['Role', 'Permission Count', 'Key Access'],
            [
                ['admin',   Permission::count() . ' (all)', 'Full system control'],
                ['coach',   '9',                            'Manage own athletes, register, view schedule'],
                ['athlete', '4',                            'View schedule & results only'],
                ['judge',   '6',                            'View schedule, input/update scores'],
            ]
        );
    }
}
