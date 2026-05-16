<?php

namespace Database\Seeders;

use App\Models\ContestScore;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ScoreSeeder - Solo Judge Scores
 * Dependencies: ContestSeeder
 */
class ScoreSeeder extends Seeder
{
    public function run(): void
    {
        $judges = User::whereHas('roles', fn($q) => $q->where('name', 'judge'))->get();
        $contests = Contest::scheduled()->get();

        DB::transaction(function () use ($contests, $judges) {
            foreach ($contests as $contest) {
                foreach ($judges as $judge) {
                    ContestScore::create([
                        'contest_id' => $contest->id,
                        'judge_id' => $judge->id,
                        'score' => rand(70, 98) / 10,  // 7.0 - 9.8
                        'notes' => 'Judge score for solo performance',
                        'scored_at' => now(),
                    ]);
                }
                // Mark contest as scored
                $contest->update(['status' => 'ongoing']);
            }
        });

        $total = ContestScore::count();
        $this->command->info("  ✅ Total Contest Scores: {$total} (avg {$judges->count()} judges/contest)");
    }
}

