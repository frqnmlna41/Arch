<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000006_create_events_table
     *
     * Command: php artisan make:migration create_events_table
     *
     * Contoh data:
     * - Kejuaraan Nasional Wushu 2024
     * - Open Tournament Wing Chun Jakarta 2024
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('location', 255);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])
                ->default('draft');
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete(); // Admin yang membuat tidak boleh langsung dihapus
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
