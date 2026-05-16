<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Registration;
use App\Models\Discipline;
use App\Models\AgeCategory;
use App\Models\Match as ScoreMatch;
use Illuminate\Support\Facades\DB;

/**
 * MatchSeeder - Solo performance matches (not vs matches)
 * Creates sequential matches for jurus/form competitions
 * Dependencies: RegistrationSeeder
 */
class MatchSeeder extends Seeder
{
    public function run(): void
    {
        $registrations = Registration::with('athlete')->get();

        DB::transaction(function () use ($registrations) {
            foreach ($registrations as $index => $reg) {
            ScoreMatch::create([
                    'registration_id' => $reg->id,
                    'discipline_id' => $reg->discipline_id,
                    'age_category_id' => $reg->age_category_id,
                    'athlete_id' => $reg->athlete_id,
                    'appearance_order' => $index + 1,
                    'match_date' => now()->addDays(1),
                    'match_time' => now()->addHours(rand(8, 18))->format('H:i:s'),
                    'venue' => 'Main Hall',
                    'status' => 'scheduled',
                ]);
            }
        });

$total = ScoreMatch::count();
        $this->command->info("  ✅ Total Matches: {$total}");
    }
}
?>

