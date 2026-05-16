<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            // Composite index for fast filtering by session and appearance order
            $table->index(['competition_session_id', 'appearance_order'], 'idx_contests_session_order');
            
            // Composite index for fast filtering by athlete and status
            $table->index(['athlete_id', 'status'], 'idx_contests_athlete_status');
        });

        // Partial Index for active matches (extremely fast for ongoing/scheduled queries)
        // We use raw statement because Blueprint doesn't support WHERE conditions in indexes natively in older Laravel, 
        // though Laravel 11/12 supports where() on indexes, raw is safer.
        DB::statement("CREATE INDEX idx_contests_active ON contests (competition_session_id) WHERE status IN ('scheduled', 'ongoing')");
        
        Schema::table('taolu_scores', function (Blueprint $table) {
            // Composite index for fast lookups per contest and judge
            $table->index(['contest_id', 'judge_id'], 'idx_taolu_scores_contest_judge');
        });
    }

    public function down(): void
    {
        Schema::table('taolu_scores', function (Blueprint $table) {
            $table->dropIndex('idx_taolu_scores_contest_judge');
        });

        DB::statement("DROP INDEX IF EXISTS idx_contests_active");

        Schema::table('contests', function (Blueprint $table) {
            $table->dropIndex('idx_contests_athlete_status');
            $table->dropIndex('idx_contests_session_order');
        });
    }
};
