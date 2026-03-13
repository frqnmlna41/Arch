<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000011_create_matches_table
     *
     * Command: php artisan make:migration create_matches_table
     *
     * Catatan:
     * - athlete2_id nullable untuk pertandingan tunggal (solo performance) di Wushu Taolu
     * - round: 'quarter_final', 'semi_final', 'final', 'bronze', atau angka babak (1,2,3...)
     * - Pertandingan bisa 1 vs 1 (Sanda) atau individual scoring (Taolu/Wing Chun Forms)
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')
                ->constrained('event_categories')
                ->cascadeOnDelete();
            $table->string('round', 50)
                ->comment('Babak: pool, quarter_final, semi_final, final, bronze, round_1 dst');
            $table->unsignedSmallInteger('match_number')->nullable()
                ->comment('Nomor urut pertandingan dalam babak ini');

            // Atlet 1 (selalu ada)
            $table->foreignId('athlete1_id')
                ->constrained('athletes')
                ->restrictOnDelete();

            // Atlet 2 nullable untuk pertandingan solo (Taolu/Forms)
            $table->foreignId('athlete2_id')
                ->nullable()
                ->constrained('athletes')
                ->nullOnDelete();

            $table->foreignId('arena_id')
                ->nullable()
                ->constrained('arenas')
                ->nullOnDelete();

            $table->date('match_date')->nullable();
            $table->time('match_time')->nullable();

            $table->enum('status', [
                'scheduled',    // Sudah dijadwalkan
                'ongoing',      // Sedang berlangsung
                'completed',    // Selesai
                'postponed',    // Ditunda
                'cancelled',    // Dibatalkan
                'walkover',     // WO (salah satu tidak hadir)
            ])->default('scheduled');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('event_category_id');
            $table->index('athlete1_id');
            $table->index('athlete2_id');
            $table->index('arena_id');
            $table->index('match_date');
            $table->index('status');
            $table->index('round');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
