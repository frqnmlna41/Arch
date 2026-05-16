<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DatabaseSeeder - COMPLETE Tournament Dataset (Solo Contests)
 * Run: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
  public function run(): void
{
    $this->command->info('🏆 Tournament CMS - Solo Contest Refactor');

    $seeders = [
        // Foundation
        'Foundation' => [
            RolePermissionSeeder::class,
            UserSeeder::class,
            SportSeeder::class,
            AgeCategorySeeder::class,
            DisciplineSeeder::class,
            DisciplineAgeCategorySeeder::class,
            ArenaSeeder::class,
            EventSeeder::class,
            EventCategorySeeder::class,
            PerguruanSeeder::class,
            AthleteSeeder::class,
        ],
        // Tournament
        // 'Tournament' => [
        //     EventParticipantSeeder::class,
        //     RegistrationSeeder::class,
        //     InvoiceSeeder::class,
        //     InvoiceItemSeeder::class,
        // ],
        // Contest Pipeline
        // 'Contest Pipeline' => [
        //     ContestSeeder::class,
        //     ScoreSeeder::class,
        //     ContestResultSeeder::class,
        //     CertificateSeeder::class,
        // ],
    ];

    foreach ($seeders as $group => $group_seeders) {
        $this->command->info("⏳ Running: {$group}...");

        foreach ($group_seeders as $seeder) {
            $name = class_basename($seeder);

            DB::beginTransaction();
            try {
                $this->call($seeder);
                DB::commit();
                $this->command->info("  ✅ {$name}");
            } catch (\Throwable $e) {
                DB::rollBack();
                // Tampilkan seeder mana yang gagal + pesan aslinya
                $this->command->error("  ❌ {$name} FAILED: " . $e->getMessage());
                // Stop seeding — jangan lanjut karena data berikutnya
                // bergantung pada seeder yang gagal ini
                return;
            }
        }

        $this->command->info("✅ {$group} complete");
        $this->command->info('');
    }

    $this->command->info('🎉 FULL SEEDING COMPLETE!');
}
}
