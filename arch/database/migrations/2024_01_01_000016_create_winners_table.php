<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->enum('medal_type', ['gold', 'silver', 'bronze'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('event_category_id');
            $table->index('athlete_id');
            $table->index('rank');

            $table->unique(['event_category_id', 'athlete_id'], 'winners_unique_athlete_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
