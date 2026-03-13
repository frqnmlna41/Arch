<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000005_create_age_categories_table
     *
     * Command: php artisan make:migration create_age_categories_table
     *
     * Contoh data:
     * - Pra-Remaja : min 8, max 12
     * - Remaja     : min 13, max 17
     * - Dewasa     : min 18, max 35
     * - Master     : min 36, max 99
     */
    public function up(): void
    {
        Schema::create('age_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedTinyInteger('min_age');
            $table->unsignedTinyInteger('max_age');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Validasi: min_age harus lebih kecil dari max_age (di-handle di aplikasi)
            $table->index('name');
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
