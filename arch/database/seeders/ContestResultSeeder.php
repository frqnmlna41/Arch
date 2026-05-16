<?php

namespace Database\Seeders;

use App\Models\ContestResult;
use App\Models\Contest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ContestResultSeeder - Solo Results
 * Dependencies: ScoreSeeder
 */
class ContestResultSeeder extends Seeder
{
    public function run(): void
    {
        $recorder = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first() ?? User::first();
        $contests = Contest::where('status', 'ongoing')->get();

        DB::transaction(function () use ($contests, $recorder) {
            foreach ($contests as $contest) {
                if (ContestResult::where('contest_id', $contest->id)->exists()) continue;

                // Average judge score for solo
                $avgScore = DB::table('contest_scores')
                    ->where('contest_id', $contest->id)
                    ->avg('score');

                ContestResult::create([
                    'contest_id' => $contest->id,
                    'final_score' => round($avgScore, 3),
                    'final_rank' => rand(1, 10),  // Will be ordered later
                    'result_type' => 'win',  // Solo always completes
                    'recorded_by' => $recorder->id,
                    'notes' => 'Demo solo result from judge average',
                ]);

                $contest->update(['status' => 'completed']);
            }
        });

        $total = ContestResult::count();
        $this->command->info("  ✅ Total Solo Results: {$total}");
    }
}

