<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000010_create_event_participants_table
     *
     * Command: php artisan make:migration create_event_participants_table
     *
     * Tabel ini mencatat pendaftaran atlet ke kategori event tertentu.
     * Seorang atlet bisa mendaftar di lebih dari satu kategori dalam event yang sama.
     *
     * Status alur:
     * pending -> verified -> rejected
     *                     -> withdrawn (mundur)
     *                     -> disqualified
     */
    public function up(): void
    {
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_category_id')
                ->constrained('event_categories')
                ->cascadeOnDelete();
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();
            $table->string('registration_number', 50)->unique()
                ->comment('Nomor pendaftaran unik, misal: WU-2024-001');
            $table->enum('status', ['pending', 'verified', 'rejected', 'withdrawn', 'disqualified'])
                ->default('pending');
            $table->decimal('weight_at_registration', 5, 2)->nullable()
                ->comment('Berat badan saat daftar (penting untuk kelas berat)');
            $table->foreignId('registered_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment('Coach atau admin yang mendaftarkan');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Index
            $table->index('event_category_id');
            $table->index('athlete_id');
            $table->index('status');
            $table->index('registration_number');
            $table->index('registered_by');

            // Satu atlet hanya boleh daftar sekali per kategori event
            $table->unique(['event_category_id', 'athlete_id'], 'event_participants_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
