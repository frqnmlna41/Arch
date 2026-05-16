<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();
            $table->foreignId('arena_id')
                ->nullable()
                ->constrained('arenas')
                ->nullOnDelete();
            $table->foreignId('sport_id')
                ->constrained('sports')
                ->restrictOnDelete();
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

            $table->index('event_id');
            $table->index('sport_id');
            $table->index('discipline_id');
            $table->index('age_category_id');
            $table->index('gender');
            $table->index('arena_id');

            $table->unique(
                ['event_id', 'discipline_id', 'age_category_id', 'gender', 'weight_class'],
                'event_categories_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_categories');
    }
};
