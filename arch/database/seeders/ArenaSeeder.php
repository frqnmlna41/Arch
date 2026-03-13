<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Arena;

/**
 * ArenaSeeder
 *
 * Membuat contoh arena/lapangan pertandingan.
 * Jumlah arena bersifat fleksibel — admin dapat menambah melalui CMS.
 *
 * Artisan command:
 *   php artisan make:seeder ArenaSeeder
 */
class ArenaSeeder extends Seeder
{
    private array $arenas = [
        [
            'name'      => 'Arena 1',
            'location'  => 'Hall A – Gedung Olahraga Nasional, Jakarta',
            'capacity'  => 500,
            'notes'     => 'Arena utama untuk pertandingan Wushu Taolu.',
            'is_active' => true,
        ],
        [
            'name'      => 'Arena 2',
            'location'  => 'Hall B – Gedung Olahraga Nasional, Jakarta',
            'capacity'  => 500,
            'notes'     => 'Arena untuk pertandingan Wushu Sanda.',
            'is_active' => true,
        ],
        [
            'name'      => 'Arena 3',
            'location'  => 'Hall C – Gedung Olahraga Nasional, Jakarta',
            'capacity'  => 300,
            'notes'     => 'Arena untuk pertandingan Wing Chun.',
            'is_active' => true,
        ],
        [
            'name'      => 'Arena 4',
            'location'  => 'Hall D – Gedung Olahraga Nasional, Jakarta',
            'capacity'  => 300,
            'notes'     => 'Arena cadangan / multi-fungsi.',
            'is_active' => true,
        ],
    ];

    public function run(): void
    {
        foreach ($this->arenas as $arena) {
            $created = Arena::firstOrCreate(
                ['name' => $arena['name']],
                $arena
            );
            $this->command->info(
                "  ✅ Arena [{$created->name}] – {$created->location} (Kapasitas: {$created->capacity})"
            );
        }
    }
}
