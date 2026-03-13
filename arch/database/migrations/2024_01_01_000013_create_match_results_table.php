<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000013_create_match_results_table
     *
     * Command: php artisan make:migration create_match_results_table
     *
     * Tabel ini menyimpan hasil akhir per pertandingan (rekap dari scores).
     *
     * Catatan:
     * - athlete1_score dan athlete2_score adalah total skor akhir
     * - winner_id nullable untuk hasil draw atau pertandingan solo tanpa lawan
     * - win_method: cara menang (KO, poin, walkover, DQ)
     */
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')
                ->unique() // Satu match hanya punya satu result
                ->constrained('matches')
                ->cascadeOnDelete();
            $table->decimal('athlete1_score', 8, 3)->nullable()
                ->comment('Total skor atlet 1');
            $table->decimal('athlete2_score', 8, 3)->nullable()
                ->comment('Total skor atlet 2');
            $table->foreignId('winner_id')
                ->nullable()
                ->constrained('athletes')
                ->nullOnDelete()
                ->comment('Pemenang pertandingan ini (null jika draw atau solo)');
            $table->enum('win_method', [
                'points',       // Menang poin
                'knockout',     // KO
                'technical_ko', // TKO
                'walkover',     // WO
                'disqualification', // DQ
                'withdrawal',   // Lawan mundur
                'scoring',      // Sistem penilaian juri (Taolu/Forms)
                'draw',         // Seri
            ])->nullable();
            $table->unsignedTinyInteger('athlete1_rounds_won')->default(0);
            $table->unsignedTinyInteger('athlete2_rounds_won')->default(0);
            $table->text('notes')->nullable()
                ->comment('Catatan hasil: alasan DQ, detail KO, dll');
            $table->foreignId('recorded_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment('Admin/operator yang merekap hasil');
            $table->timestamps();

            // Index
            $table->index('match_id');
            $table->index('winner_id');
            $table->index('win_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
