<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration tambahan untuk mendukung seeder:
 *
 * 1. Menambahkan kolom sport_id ke age_categories
 *    (agar kategori umur bisa berbeda per sport)
 *
 * 2. Menambahkan kolom type & match_type ke disciplines
 *    (type: empty_hand|weapon, match_type: performance|sparring)
 *
 * 3. Membuat tabel pivot discipline_age_categories
 *
 * Command:
 *   php artisan make:migration add_sport_type_columns_and_create_pivot_tables
 *
 * CATATAN:
 * Jika migration sebelumnya sudah include kolom-kolom ini,
 * gunakan migration ini hanya untuk bagian yang belum ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Tambah sport_id ke age_categories ──────────────────
        // Agar kategori umur bisa berbeda antar sport (Wushu vs Wing Chun)
        if (Schema::hasTable('age_categories') && ! Schema::hasColumn('age_categories', 'sport_id')) {
            Schema::table('age_categories', function (Blueprint $table) {
                $table->foreignId('sport_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('sports')
                    ->cascadeOnDelete();

                $table->string('label', 150)->nullable()->after('name')
                    ->comment('Label panjang, misal: Kategori D (Di bawah 8 tahun)');

                $table->index('sport_id');
            });
        }

        // ── 2. Tambah type & match_type ke disciplines ─────────────
        if (Schema::hasTable('disciplines')) {
            Schema::table('disciplines', function (Blueprint $table) {
                if (! Schema::hasColumn('disciplines', 'type')) {
                    $table->enum('type', ['empty_hand', 'weapon'])
                        ->default('empty_hand')
                        ->after('name')
                        ->comment('Jenis: empty_hand (tangan kosong) atau weapon (senjata)');
                }
                if (! Schema::hasColumn('disciplines', 'match_type')) {
                    $table->enum('match_type', ['performance', 'sparring'])
                        ->default('performance')
                        ->after('type')
                        ->comment('Tipe pertandingan: performance (Taolu/Forms) atau sparring (Sanda/Combat)');
                }
            });
        }

        // ── 3. Buat tabel pivot discipline_age_categories ──────────
        if (! Schema::hasTable('discipline_age_categories')) {
            Schema::create('discipline_age_categories', function (Blueprint $table) {
                $table->foreignId('discipline_id')
                    ->constrained('disciplines')
                    ->cascadeOnDelete();

                $table->foreignId('age_category_id')
                    ->constrained('age_categories')
                    ->cascadeOnDelete();

                // Composite primary key untuk mencegah duplikat
                $table->primary(
                    ['discipline_id', 'age_category_id'],
                    'discipline_age_category_primary'
                );

                // Index untuk query dari kedua arah
                $table->index('discipline_id');
                $table->index('age_category_id');
            });
        }
    }

    public function down(): void
    {
        // Drop pivot terlebih dahulu (ada FK)
        Schema::dropIfExists('discipline_age_categories');

        // Hapus kolom dari disciplines
        if (Schema::hasTable('disciplines')) {
            Schema::table('disciplines', function (Blueprint $table) {
                $table->dropColumnIfExists('match_type');
                $table->dropColumnIfExists('type');
            });
        }

        // Hapus kolom dari age_categories
        if (Schema::hasTable('age_categories')) {
            Schema::table('age_categories', function (Blueprint $table) {
                $table->dropForeign(['sport_id']);
                $table->dropColumnIfExists('sport_id');
                $table->dropColumnIfExists('label');
            });
        }
    }
};
