<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('winner_id')
                ->constrained('winners')
                ->cascadeOnDelete();
            $table->string('certificate_number', 100)->unique()
                ->comment('Nomor sertifikat unik, misal: CERT-2024-00001');
            $table->string('file_path')->nullable()
                ->comment('Path ke file PDF sertifikat');
            $table->timestamp('issued_at')->nullable()
                ->comment('Tanggal dan waktu sertifikat diterbitkan');
            $table->boolean('is_printed')->default(false)
                ->comment('Apakah sertifikat sudah dicetak secara fisik');
            $table->timestamp('printed_at')->nullable();
            $table->foreignId('issued_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin yang menerbitkan sertifikat');
            $table->string('template_version', 20)->nullable()
                ->comment('Versi template yang digunakan saat generate');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('winner_id');
            $table->index('issued_at');
            $table->index('is_printed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
