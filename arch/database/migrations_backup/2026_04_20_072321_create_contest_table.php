<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Match = satu slot penampilan untuk satu athlete (jurus/solo)
        Schema::create('contest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->cascadeOnDelete();
            $table->foreignId('discipline_id')->constrained('disciplines')->cascadeOnDelete();
            $table->foreignId('age_category_id')->constrained('age_categories')->cascadeOnDelete();
            $table->foreignId('athlete_id')->constrained('athletes')->cascadeOnDelete();

            $table->integer('appearance_order'); // urutan tampil (1, 2, 3, ...)
            $table->date('match_date')->nullable();
            $table->time('match_time')->nullable();
            $table->string('venue')->nullable();

            $table->enum('status', ['scheduled', 'ongoing', 'done', 'cancelled'])->default('scheduled');

            // Nilai akhir — dihitung dari rata-rata score judge
            $table->decimal('final_score', 6, 2)->nullable();
            $table->integer('final_rank')->nullable(); // ranking setelah semua tampil

            $table->timestamps();
        });

        // Nilai dari masing-masing judge per match
        Schema::create('contest_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained('contest')->cascadeOnDelete();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete(); // judge = user dengan role judge
            $table->decimal('score', 6, 2);
            $table->text('notes')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();

            // Satu judge hanya bisa input nilai sekali per match
            $table->unique(['contest_id', 'judge_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_scores');
        Schema::dropIfExists('contest');
    }
};
