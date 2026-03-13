<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000009_create_event_categories_table
     *
     * Command: php artisan make:migration create_event_categories_table
     *
     * Tabel ini adalah "kategori lomba" dalam sebuah event.
     * Contoh: Event "Kejurnas Wushu 2024" memiliki kategori:
     * - Sanda Putra Remaja 52kg
     * - Taolu Putri Dewasa
     * - Wing Chun Chi Sao Putra Master
     */
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->foreignId('sport_id')
                ->constrained('sports')
                ->restrictOnDelete(); // Sport tidak boleh dihapus jika masih ada kategori event
            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->restrictOnDelete();
            $table->foreignId('age_category_id')
                ->constrained('age_categories')
                ->restrictOnDelete();
            $table->enum('gender', ['male', 'female', 'mixed'])
                ->comment('Kategori gender: male=Putra, female=Putri, mixed=Campuran');
            $table->string('weight_class', 50)->nullable()
                ->comment('Kelas berat: misal 52kg, 60kg, open');
            $table->integer('max_participants')->nullable()
                ->comment('Maksimum peserta dalam kategori ini');
            $table->enum('format', ['single_elimination', 'double_elimination', 'round_robin', 'scoring'])
                ->default('single_elimination')
                ->comment('Format pertandingan');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index('event_id');
            $table->index('sport_id');
            $table->index('discipline_id');
            $table->index('age_category_id');
            $table->index('gender');

            // Kombinasi unik: satu event tidak boleh punya duplikat kategori yang sama persis
            $table->unique(
                ['event_id', 'discipline_id', 'age_category_id', 'gender', 'weight_class'],
                'event_categories_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
