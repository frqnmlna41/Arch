<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registration;
use App\Models\Athlete;
use App\Models\EventCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * RegistrationSeeder
 *
 * Mensimulasikan ALUR COACH:
 *   Coach login → Pilih atlet → Pilih event category (discipline+ageCategory) → Submit registrasi
 *   Status awal: 'pending' (menunggu verifikasi admin)
 *
 * DEPENDENCIES: UserSeeder, AthleteSeeder, EventCategorySeeder
 */
class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $coaches         = User::role('coach')->get();
        $eventCategories = EventCategory::with(['discipline', 'ageCategory'])->get();
        $athletes        = Athlete::all();

        if ($coaches->isEmpty()) {
            $this->command->error('❌ Tidak ada coach. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        if ($eventCategories->isEmpty()) {
            $this->command->error('❌ Tidak ada event category. Jalankan EventCategorySeeder terlebih dahulu.');
            return;
        }

        if ($athletes->isEmpty()) {
            $this->command->error('❌ Tidak ada atlet. Jalankan AthleteSeeder terlebih dahulu.');
            return;
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($coaches, $eventCategories, $athletes, &$created, &$skipped) {
            foreach ($athletes as $athlete) {
                // Setiap atlet didaftarkan coach-nya ke 1-2 kategori event
                $coach = $coaches->firstWhere('id', $athlete->coach_id) ?? $coaches->first();

                // Pilih 1-2 kategori event yang gender-nya cocok dengan atlet
                $matchingCategories = $eventCategories->filter(function ($cat) use ($athlete) {
                    return $cat->gender === $athlete->gender || $cat->gender === 'mixed';
                })->take(2);

                if ($matchingCategories->isEmpty()) {
                    $matchingCategories = $eventCategories->take(1);
                }

                foreach ($matchingCategories as $category) {
                    // Cek duplikat berdasarkan unique constraint di migration
                    $exists = Registration::where('athlete_id', $athlete->id)
                        ->where('discipline_id', $category->discipline_id)
                        ->where('age_category_id', $category->age_category_id)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    Registration::create([
                        'user_id'          => $coach->id,
                        'athlete_id'       => $athlete->id,
                        'event_category_id'=> $category->id,
                        'discipline_id'    => $category->discipline_id,
                        'age_category_id'  => $category->age_category_id,
                        'status'           => 'pending',   // ← Coach submit, admin belum approve
                        'registered_at'    => now()->subDays(rand(3, 10)),
                    ]);

                    $created++;
                }
            }
        });

        $this->command->info("  ✅ Registrations: {$created} dibuat, {$skipped} dilewati (duplikat).");
        $this->command->info('     Total registrations: ' . Registration::count());
    }
}
