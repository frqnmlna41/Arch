<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->comment('Coach/User pemilik invoice');
            $table->string('invoice_number', 50)->unique();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('due_date');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->restrictOnDelete();
            $table->foreignId('event_participant_id')
                ->constrained('event_participants')
                ->restrictOnDelete();
            $table->foreignId('discipline_id')
                ->constrained('disciplines')
                ->restrictOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('athlete_id');
            $table->index('event_participant_id');
            $table->unique(
                ['invoice_id', 'athlete_id', 'event_participant_id'],
                'invoice_items_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
