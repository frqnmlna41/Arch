<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('age_categories', function (Blueprint $table) {
            $table->id();

            // Relasi ke sport
            $table->foreignId('sport_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('label', 150); // ← kolom baru

            $table->unsignedTinyInteger('min_age');
            $table->unsignedTinyInteger('max_age');

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Unique per sport
            $table->unique(['sport_id', 'name']);

            $table->index(['min_age', 'max_age']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_categories');
    }
};
