<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000014_create_winners_table
     *
     * Command: php artisan make:migration create_winners_table
     *
     * Tabel ini mencatat pemenang akhir per kategori event (juara 1, 2, 3).
     * Diisi setelah seluruh pertandingan dalam kategori selesai.
     *
     * Catatan:
     * - rank 1 = Juara 1 (Emas), 2 = Perak, 3 = Perunggu
     * - Bisa ada 2 atlet di rank 3 (dua perunggu, tanpa final perebutan juara 3)
     * - total_score untuk kategori scoring (Taolu, Forms)
     */
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')
                ->constrained('event_categories')
                ->cascadeOnDelete();
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rank')
                ->comment('Peringkat: 1=Emas, 2=Perak, 3=Perunggu');
            $table->decimal('total_score', 8, 3)->nullable()
                ->comment('Total skor akhir (untuk kategori penilaian)');
            $table->string('medal_type', 20)->nullable()
                ->comment('gold, silver, bronze');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('event_category_id');
            $table->index('athlete_id');
            $table->index('rank');

            // Satu atlet hanya punya satu rank per kategori
            $table->unique(['event_category_id', 'athlete_id'], 'winners_unique_athlete_category');

            // Rank 1 dan 2 hanya boleh ada satu per kategori
            // Rank 3 bisa ada 2 (perunggu ganda) - dikontrol di level aplikasi
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
