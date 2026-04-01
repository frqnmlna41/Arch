<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * UserSeeder
 *
 * Membuat user default untuk setiap role dalam sistem.
 *
 * Artisan command:
 *   php artisan make:seeder UserSeeder
 *
 * User yang dibuat:
 *   - Super Admin  (admin)
 *   - Coach Demo   (coach)
 *   - Judge Demo   (judge)
 *   - Athlete Demo (athlete)
 *
 * PENTING: RolePermissionSeeder harus dijalankan terlebih dahulu.
 */
class UserSeeder extends Seeder
{
    /**
     * Definisi user default.
     */
    private array $users = [
        [
            'name'     => 'Super Admin',
            'email'    => 'admin@tournament.com',
            'password' => 'password',
            'phone'    => '081200000001',
            'role'     => 'admin',
        ],
        [
            'name'     => 'Coach Demo',
            'email'    => 'coach@tournament.com',
            'password' => 'password',
            'phone'    => '081200000002',
            'role'     => 'coach',
        ],
        [
            'name'     => 'Judge Demo',
            'email'    => 'judge@tournament.com',
            'password' => 'password',
            'phone'    => '081200000003',
            'role'     => 'judge',
        ],
        [
            'name'     => 'Athlete Demo',
            'email'    => 'athlete@tournament.com',
            'password' => 'password',
            'phone'    => '081200000004',
            'role'     => 'athlete',
        ],
    ];

    public function run(): void
    {
        foreach ($this->users as $userData) {
            $role = $userData['role'];
            unset($userData['role']); // Hapus key 'role' sebelum create

            // firstOrCreate agar tidak duplikat saat re-seed
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name'              => $userData['name'],
                    'password'          => Hash::make($userData['password']),
                    'phone'             => $userData['phone'],
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Assign role (syncRoles agar tidak duplikat)
            $user->syncRoles([$role]);

            // Force status active for all seeded users
            $user->update(['status' => 'active']);

            $this->command->info("  ✅ User [{$user->email}] created → role: {$role}, status: active");
        }
    }
}
