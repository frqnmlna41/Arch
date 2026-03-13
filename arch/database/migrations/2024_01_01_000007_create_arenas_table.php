<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000007_create_arenas_table
     *
     * Command: php artisan make:migration create_arenas_table
     *
     * Dibuat sebelum athletes karena tidak ada dependency ke tabel lain,
     * tapi dibutuhkan oleh matches.
     *
     * Contoh data:
     * - Arena A - GOR Senayan, Jakarta
     * - Arena B - GOR Senayan, Jakarta
     * - Gelanggang 1 - Balai Kota Bandung
     */
    public function up(): void
    {
        Schema::create('arenas', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('location', 255);
            $table->integer('capacity')->nullable()->comment('Kapasitas penonton');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index
            $table->index('name');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arenas');
    }
};
