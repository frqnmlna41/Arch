<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->comment('Coach/User who registered');
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();
            $table->foreignId('event_category_id')
                ->constrained('event_categories')
                ->cascadeOnDelete();
            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->cascadeOnDelete();
            $table->foreignId('age_category_id')
                ->constrained('age_categories')
                ->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();

            $table->unique(['athlete_id', 'discipline_id', 'age_category_id']);
            $table->index(['status', 'registered_at']);
            $table->index('event_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
