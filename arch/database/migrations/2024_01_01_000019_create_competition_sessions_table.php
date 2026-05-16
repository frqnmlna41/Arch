<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')
                ->constrained('event_categories')
                ->cascadeOnDelete();
            $table->foreignId('arena_id')
                ->nullable()
                ->constrained('arenas')
                ->nullOnDelete();
            $table->enum('gender', ['male', 'female', 'mixed']);
            $table->dateTime('start_time');
            $table->unsignedSmallInteger('duration_per_athlete')->default(4)
                ->comment('Durasi per atlet dalam menit');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'ongoing', 'done'])->default('draft');
            $table->timestamps();

            $table->unique(['event_category_id', 'gender'], 'unique_session_per_category_gender');
            $table->index('arena_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_sessions');
    }
};
