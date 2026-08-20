<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('service_type')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('cnic', 20)->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hall_id', 'is_active']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('vendor_id')->references('id')->on('vendors')->nullOnDelete();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('cnic', 20)->nullable();
            $table->text('address')->nullable();
            $table->decimal('monthly_salary', 10, 2)->nullable();
            $table->enum('employment_type', ['permanent', 'daily_wage', 'contract'])->default('permanent');
            $table->date('joined_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hall_id', 'is_active']);
        });

        // Which staff are rostered onto which event.
        Schema::create('booking_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->decimal('wage', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['booking_id', 'staff_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_staff');
        Schema::dropIfExists('staff');
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
        });
        Schema::dropIfExists('vendors');
    }
};
