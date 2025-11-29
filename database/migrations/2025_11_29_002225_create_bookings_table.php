<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');

            $table->decimal('quote_price', 10, 2);
            $table->decimal('booking_price', 10, 2)->nullable();
            $table->decimal('advance_paid', 10, 2)->default(0);

            $table->integer('capacity')->nullable();
            $table->json('facilities')->nullable();

            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');

            $table->text('notes')->nullable();
            $table->string('booking_number')->unique()->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
