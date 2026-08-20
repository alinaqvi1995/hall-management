<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payment ledger. Replaces the single `advance_paid` column, which could not
 * represent the instalments a hall actually collects (booking advance, second
 * instalment, settlement on the event day).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'cheque', 'card', 'easypaisa', 'jazzcash', 'other'])
                ->default('cash');
            $table->enum('direction', ['in', 'refund'])->default('in');
            $table->string('reference')->nullable();
            $table->date('paid_on');
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hall_id', 'paid_on']);
            $table->index(['booking_id', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
