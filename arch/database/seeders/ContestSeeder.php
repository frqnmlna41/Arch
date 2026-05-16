<?php

namespace Database\Seeders;

use App\Models\Contest;
use App\Models\EventCategory;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ContestSeeder - Solo Performance Contests
 * Dependencies: EventParticipantSeeder
 */
class ContestSeeder extends Seeder
{
    public function run(): void
    {
        $categories = EventCategory::with(['eventParticipants' => fn($q) => $q->verified(), 'discipline'])->get();

        DB::transaction(function () use ($categories) {
            foreach ($categories as $category) {
                $participants = $category->eventParticipants()->verified()->get();

                if ($participants->count() === 0) continue;

                foreach ($participants as $index => $participant) {
                    // Fallback coach ID (coach@tournament.com = ID 2)
                    $coachId = $participant->registeredBy ? $participant->registeredBy->id : 2;

                    $registration = Registration::firstOrCreate(
                        [
                            'user_id' => $coachId,
                            'athlete_id' => $participant->athlete_id,
                            'discipline_id' => $category->discipline_id,
                            'age_category_id' => $category->age_category_id,
                        ],
                        [
                            'status' => 'approved',
                        ]
                    );

                    Contest::create([
                        'registration_id' => $registration->id,
                        'athlete_id' => $participant->athlete_id,
                        'discipline_id' => $category->discipline_id,
                        'age_category_id' => $category->age_category_id,
                        'appearance_order' => $index + 1,
                        'match_date' => now()->addDays(rand(1, 7)),
                        'status' => 'scheduled',
                    ]);
                }

                $this->command->info("  ✅ Category '{$category->notes}': {$participants->count()} solo contests");
            }
        });
    }
}

