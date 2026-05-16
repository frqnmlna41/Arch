<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perguruan;

/**
 * PerguruanSeeder
 * Creates martial arts schools/clubs/dojos for tournament
 */
class PerguruanSeeder extends Seeder
{
    private array $perguruans = [
        [
            'name' => 'Bulan Matahari',
            'address' => 'Jl. Sudirman No.45, Jakarta',
            'phone' => '021-1234567',
            'is_active' => true,
        ],
        [
            'name' => 'Harimau Putih',
            'address' => 'Jl. Gatot Subroto, Bandung',
            'phone' => '022-7654321',
            'is_active' => true,
        ],
    ];

    public function run(): void
    {
        foreach ($this->perguruans as $data) {
            $perguruan = Perguruan::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
            $this->command->info("  ✅ Perguruan [{$perguruan->name}] seeded. (ID: {$perguruan->id})");
        }
    }
}
?>

