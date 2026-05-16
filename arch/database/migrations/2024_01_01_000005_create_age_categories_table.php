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
            $table->foreignId('sport_id')
                ->constrained('sports')
                ->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('label', 150)->nullable()
                ->comment('Label panjang, misal: Kategori D (Di bawah 8 tahun)');
            $table->unsignedTinyInteger('min_age');
            $table->unsignedTinyInteger('max_age');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sport_id', 'name']);
            $table->index('sport_id');
            $table->index(['min_age', 'max_age']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_categories');
    }
};
