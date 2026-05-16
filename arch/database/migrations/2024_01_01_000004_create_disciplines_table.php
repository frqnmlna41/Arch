<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disciplines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')
                ->constrained('sports')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['empty_hand', 'weapon'])->default('empty_hand')
                ->comment('Jenis: empty_hand atau weapon');
            $table->enum('match_type', ['performance', 'sparring'])->default('performance')
                ->comment('Tipe pertandingan: performance (Taolu/Forms) atau sparring (Sanda/Combat)');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('sport_id');
            $table->index('name');
            $table->unique(['sport_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplines');
    }
};
