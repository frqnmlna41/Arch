<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000012_create_scores_table
     *
     * Command: php artisan make:migration create_scores_table
     *
     * Tabel ini mencatat nilai dari setiap juri untuk setiap atlet per pertandingan.
     *
     * Untuk Wushu Taolu: setiap juri memberi nilai (misal 9.5, 9.8)
     * Untuk Wushu Sanda: poin per ronde (juri menghitung poin)
     * Untuk Wing Chun: nilai teknik per aspek
     *
     * Catatan:
     * - score menggunakan decimal untuk fleksibilitas
     * - score_type untuk membedakan jenis penilaian
     */
    public function up(): void
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')
                ->constrained('matches')
                ->cascadeOnDelete();
            $table->foreignId('judge_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment('Juri yang memberikan nilai');
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete()
                ->comment('Atlet yang dinilai');
            $table->decimal('score', 6, 2)
                ->comment('Nilai yang diberikan');
            $table->string('score_type', 50)->nullable()
                ->comment('Jenis penilaian: technique, difficulty, deduction, total, round_1 dst');
            $table->unsignedTinyInteger('round_number')->nullable()
                ->comment('Nomor ronde (untuk Sanda)');
            $table->text('notes')->nullable()
                ->comment('Catatan juri: alasan pengurangan nilai, dll');
            $table->timestamps();

            // Index
            $table->index('match_id');
            $table->index('judge_id');
            $table->index('athlete_id');
            $table->index('score_type');

            // Satu juri hanya boleh memberi satu nilai per tipe per atlet per pertandingan
            $table->unique(
                ['match_id', 'judge_id', 'athlete_id', 'score_type', 'round_number'],
                'scores_unique_per_judge'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scores');
    }
};
