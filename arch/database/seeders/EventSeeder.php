<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;

/**
 * EventSeeder
 *
 * Membuat contoh event kejuaraan untuk demo sistem.
 *
 * Artisan command:
 *   php artisan make:seeder EventSeeder
 *
 * PENTING: UserSeeder harus dijalankan sebelum ini
 * karena membutuhkan user admin sebagai 'created_by'.
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan admin sebagai creator event
        $admin = User::where('email', 'admin@tournament.com')->firstOrFail();

        $events = [
            [
                'name'               => 'Kejuaraan Wushu & Wing Chun Nasional 2026',
                'location'           => 'Gedung Olahraga Nasional, Jakarta',
                'start_date'         => '2026-07-01',
                'end_date'           => '2026-07-05',
                'registration_start' => '2026-05-01',
                'registration_end'   => '2026-06-15',
                'status'             => 'published',
                'description'        => 'Kejuaraan Nasional Wushu & Wing Chun 2026 merupakan ajang '
                                     . 'bergengsi yang mempertemukan atlet-atlet terbaik dari seluruh '
                                     . 'Indonesia. Pertandingan mencakup cabang Wushu Taolu dan Wing Chun '
                                     . 'Kungfu dengan berbagai kategori usia dan disiplin.',
                'created_by'         => $admin->id,
            ],
        ];

        foreach ($events as $eventData) {
            $event = Event::firstOrCreate(
                ['name' => $eventData['name']],
                $eventData
            );

            $this->command->info("  ✅ Event [{$event->name}]");
            $this->command->info("     📅 {$event->start_date} s/d {$event->end_date}");
            $this->command->info("     📍 {$event->location}");
            $this->command->info("     🔖 Status: {$event->status}");
        }
    }
}
