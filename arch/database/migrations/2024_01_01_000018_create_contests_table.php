<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();
            // $table->foreignId('event_category_id')
            //     ->constrained('event_categories')
            //     ->cascadeOnDelete();
            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->cascadeOnDelete();
            $table->foreignId('age_category_id')
                ->constrained('age_categories')
                ->cascadeOnDelete();
            $table->integer('appearance_order');    
            $table->unsignedBigInteger('competition_session_id')->nullable();
            $table->foreignId('registration_id')
                ->constrained('registrations')
                ->cascadeOnDelete();
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])
                ->default('scheduled');
            $table->timestamps();

            // $table->index('event_category_id');
            $table->index('age_category_id');
            $table->index('discipline_id');
            $table->index('competition_session_id');
            $table->index('registration_id');
            $table->index('status');
        });

        Schema::create('contest_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')
                ->unique()
                ->constrained('contests')
                ->cascadeOnDelete();
            $table->decimal('final_score', 8, 3)->nullable();
            $table->integer('final_rank')->nullable();
            $table->enum('result_type', ['win', 'loss', 'cancelled'])->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamps();

            $table->index('final_rank');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_results');
        Schema::dropIfExists('contests');
    }
};
