<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Priced extras (stage decor, DJ, photography, cold drinks...). Replaces the
 * free-text `facilities` JSON on bookings, which carried no price.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('pricing_mode', ['fixed', 'per_head'])->default('fixed');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hall_id', 'name']);
            $table->index(['hall_id', 'is_active']);
        });

        Schema::create('booking_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained('addons')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            // Price is copied at booking time so later catalogue edits never
            // rewrite the amount a customer already agreed to.
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['booking_id', 'addon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_addon');
        Schema::dropIfExists('addons');
    }
};
