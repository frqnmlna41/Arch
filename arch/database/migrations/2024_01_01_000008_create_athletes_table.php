<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migration: 2024_01_01_000008_create_athletes_table
     *
     * Command: php artisan make:migration create_athletes_table
     *
     * Catatan:
     * - coach_id merujuk ke users dengan role 'coach'
     * - Seorang coach bisa mendaftarkan banyak atlet
     * - Atlet bisa juga memiliki akun user (nullable user_id)
     */
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete() // Jika akun user dihapus, data atlet tetap ada
                ->comment('Akun user milik atlet (opsional)');
            $table->foreignId('coach_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete() // Jika coach dihapus, atlet tidak ikut terhapus
                ->comment('Coach yang mendaftarkan atlet ini');
            $table->string('name', 150);
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('club', 150)->nullable()->comment('Nama klub/perguruan');
            $table->string('phone', 20)->nullable();
            $table->string('photo')->nullable()->comment('Path foto profil atlet');
            $table->string('id_card_number', 50)->nullable()->comment('NIK/No. KTP/No. Kartu Pelajar');
            $table->decimal('weight', 5, 2)->nullable()->comment('Berat badan dalam kg');
            $table->decimal('height', 5, 2)->nullable()->comment('Tinggi badan dalam cm');
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('coach_id');
            $table->index('user_id');
            $table->index('gender');
            $table->index('name');
            $table->index('is_active');
            $table->index('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};
