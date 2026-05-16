<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000004_create_disciplines_table
     *
     * Command: php artisan make:migration create_disciplines_table
     *
     * Contoh data Wushu:
     * - Taolu (Sanda, Changquan, Nanquan, Taijiquan)
     * - Sanda (Full Contact)
     *
     * Contoh data Wing Chun:
     * - Chi Sao
     * - Forms (Siu Nim Tao, Chum Kiu, Biu Tze)
     */
    public function up(): void
    {
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')
                ->constrained('sports')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index
            $table->index('sport_id');
            $table->index('name');
            $table->unique(['sport_id', 'name']); // Nama discipline unik per sport
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplines');
    }
};
