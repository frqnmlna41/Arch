<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Athlete;
use App\Models\Event;
use App\Models\User;
use App\Models\Discipline;
use App\Models\AgeCategory;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Auth;

class RegistrationServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menggunakan RegistrationService agar semua relasi (Invoice, Contest, dsb) 
     * tergenerate dengan benar sesuai service class.
     */
    public function run(RegistrationService $registrationService): void
    {
        $coach = User::role('coach')->first();
        $athletes = Athlete::all();
        $event = Event::where('status', 'published')->first();
        $disciplines = Discipline::all();
        $ageCategories = AgeCategory::all();

        if (!$event) {
            $this->command->error('❌ Tidak ada event aktif (published). Jalankan EventSeeder terlebih dahulu.');
            return;
        }

        if (!$coach || $athletes->isEmpty() || $disciplines->isEmpty() || $ageCategories->isEmpty()) {
            $this->command->error('❌ Pastikan ada User Coach, dan seeder Athlete, Discipline, AgeCategory sudah dijalankan.');
            return;
        }

        $maleAthletes = $athletes->where('gender', 'male')->values();
        $femaleAthletes = $athletes->where('gender', 'female')->values();

        if ($maleAthletes->count() < 6 || $femaleAthletes->count() < 6) {
            $this->command->warn('⚠️ Jumlah atlet laki-laki/perempuan kurang dari 6. Akan menggunakan semua yang ada.');
        }

        $created = 0;
        Auth::login($coach);

        // Kita kumpulkan item pendaftaran per atlet agar di-submit sekaligus
        $athleteRegistrations = [];

        // Untuk setiap disiplin, kita buat minimal 1 sesi yang berisi 5-6 atlet
        foreach ($disciplines as $discipline) {
            $targetAgeCategory = $ageCategories->random();

            // Ambil 5-6 atlet male (atau maksimal yang ada)
            $takeMale = min(rand(5, 6), $maleAthletes->count());
            if ($takeMale > 0) {
                $males = $maleAthletes->random($takeMale);
                foreach ($males as $athlete) {
                    $athleteRegistrations[$athlete->id][] = [
                        'discipline_id' => $discipline->id,
                        'age_category_id' => $targetAgeCategory->id,
                    ];
                }
            }

            // Ambil 5-6 atlet female (atau maksimal yang ada)
            $takeFemale = min(rand(5, 6), $femaleAthletes->count());
            if ($takeFemale > 0) {
                $females = $femaleAthletes->random($takeFemale);
                foreach ($females as $athlete) {
                    $athleteRegistrations[$athlete->id][] = [
                        'discipline_id' => $discipline->id,
                        'age_category_id' => $targetAgeCategory->id,
                    ];
                }
            }
        }

        foreach ($athleteRegistrations as $athleteId => $items) {
            $athlete = $athletes->firstWhere('id', $athleteId);
            
            // Set coach_id ke 5 agar konsisten
            $athlete->update(['coach_id' => $coach->id]);
            
            try {
                $registrationService->registerDisciplines($athlete, $items);
                $created++;
            } catch (\Exception $e) {
                $this->command->error("❌ Gagal mendaftarkan atlet {$athlete->name}: " . $e->getMessage());
            }
        }

        $this->command->info("  ✅ Pendaftaran via RegistrationService selesai. Total transaksi/atlet: {$created}");
    }
}
