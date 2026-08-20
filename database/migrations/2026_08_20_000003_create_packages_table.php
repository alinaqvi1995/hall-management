<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catering packages / menus. Pakistani marquees quote a per-head rate against a
 * named menu, so the package carries the rate and the booking multiplies it by
 * the guest count.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['buffet', 'set_menu', 'one_dish', 'custom'])->default('buffet');
            $table->decimal('per_head_rate', 10, 2);
            $table->unsignedInteger('min_guests')->default(0);
            $table->text('description')->nullable();
            $table->json('items')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['hall_id', 'name']);
            $table->index(['hall_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
