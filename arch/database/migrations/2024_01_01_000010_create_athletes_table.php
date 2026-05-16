<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Akun user milik atlet (opsional)');
            $table->foreignId('coach_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Coach yang mendaftarkan atlet ini');
            $table->foreignId('perguruan_id')
                ->nullable()
                ->constrained('perguruans')
                ->nullOnDelete()
                ->comment('Perguruan tempat atlet terdaftar');
            $table->string('name', 150);
            $table->date('birth_date');
            $table->enum('gender', ['male', 'female']);
            $table->string('club', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('photo')->nullable();
            $table->string('id_card_number', 50)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['coach_id', 'is_active']);
            $table->index('perguruan_id');
            $table->index('user_id');
            $table->index('gender');
            $table->index('name');
            $table->index('birth_date');
        });

        Schema::create('athlete_discipline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();
            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->cascadeOnDelete();
            $table->foreignId('age_category_id')
                ->constrained('age_categories')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['athlete_id', 'discipline_id', 'age_category_id']);
        });

        Schema::create('discipline_age_categories', function (Blueprint $table) {
            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->cascadeOnDelete();
            $table->foreignId('age_category_id')
                ->constrained('age_categories')
                ->cascadeOnDelete();

            $table->primary(
                ['discipline_id', 'age_category_id'],
                'discipline_age_category_primary'
            );

            $table->index('discipline_id');
            $table->index('age_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_age_categories');
        Schema::dropIfExists('athlete_discipline');
        Schema::dropIfExists('athletes');
    }
};
