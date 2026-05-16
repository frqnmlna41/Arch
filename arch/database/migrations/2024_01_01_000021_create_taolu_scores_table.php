<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taolu_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')
                ->unique()
                ->constrained('contests')
                ->cascadeOnDelete();
            $table->decimal('judge_1', 5, 2)->nullable();
            $table->decimal('judge_2', 5, 2)->nullable();
            $table->decimal('judge_3', 5, 2)->nullable();
            $table->decimal('judge_4', 5, 2)->nullable();
            $table->decimal('judge_5', 5, 2)->nullable();
            $table->decimal('deduction', 5, 2)->default(0);
            $table->decimal('final_score', 6, 3)->nullable()
                ->comment('Disimpan agar tidak dihitung ulang tiap query');
            $table->foreignId('inputted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('inputted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taolu_scores');
    }
};
