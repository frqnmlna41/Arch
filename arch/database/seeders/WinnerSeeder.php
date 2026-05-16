<?php

namespace Database\Seeders;

use App\Models\ContestResult;
use App\Models\EventCategory;
use App\Models\Winner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * WinnerSeeder - Solo Winners from Contest Results
 * Dependencies: ContestResultSeeder
 */
class WinnerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();

        $results = ContestResult::with('contest.registration', 'contest.athlete')->get();

        DB::transaction(function () use ($results) {
            foreach ($results as $result) {
                $contest = $result->contest;
                $category = $contest->registration->discipline->pivot_age_category ?? EventCategory::first(); // Fallback

                Winner::firstOrCreate(
                    [
                        'event_id' => 1, // Demo event
                        'athlete_id' => $contest->athlete_id,
                    ],
                    [
                        'event_category_id' => $category->id ?? 1,
                        'discipline_id' => $contest->discipline_id,
                        'age_category_id' => $contest->age_category_id,
                        'rank' => rand(1, 3), // Gold, silver, bronze simulation
                        'score' => $result->final_score,
                        'recorded_by' => $user?->id ?? 1, // Fallback to admin ID
                    ]
                );
            }
        });

        $total = Winner::count();
        $this->command->info("  🏆 Total Winners: {$total} (from solo contests)");
    }
}

