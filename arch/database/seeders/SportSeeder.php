<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sport;

/**
 * SportSeeder
 *
 * Membuat data sport utama: Wushu dan Wing Chun.
 *
 * Artisan command:
 *   php artisan make:seeder SportSeeder
 */
class SportSeeder extends Seeder
{
    private array $sports = [
        [
            'name'        => 'Wushu',
            'description' => 'Seni bela diri tradisional Tiongkok yang mencakup gerakan teknik '
                           . '(Taolu) dan pertarungan bebas (Sanda). Diakui secara internasional '
                           . 'dan dipertandingkan di berbagai kejuaraan dunia.',
            'is_active'   => true,
        ],
        [
            'name'        => 'Wing Chun',
            'description' => 'Sistem bela diri Kung Fu yang berfokus pada pertarungan jarak dekat, '
                           . 'penggunaan energi efisien, dan teknik tangan yang cepat dan tepat. '
                           . 'Dipopulerkan oleh Ip Man dan Bruce Lee.',
            'is_active'   => true,
        ],
    ];

    public function run(): void
    {
        foreach ($this->sports as $sport) {
            $created = Sport::firstOrCreate(
                ['name' => $sport['name']],
                $sport
            );
            $this->command->info("  ✅ Sport [{$created->name}] seeded. (ID: {$created->id})");
        }
    }
}
