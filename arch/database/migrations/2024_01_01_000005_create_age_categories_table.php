<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_categories', function (Blueprint $table) {
            $table->id();

            // RELATION
            $table->foreignId('sport_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // DATA
            $table->string('name', 50); // A, B, C1, dst
            $table->string('label');
            $table->unsignedTinyInteger('min_age');
            $table->unsignedTinyInteger('max_age');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // ✅ FIX UTAMA (bukan unique global)
            $table->unique(['sport_id', 'name']);

            // INDEX
            $table->index(['min_age', 'max_age']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_categories');
    }
};
