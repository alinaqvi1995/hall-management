<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('lawn_id')->nullable()->after('hall_id');

            // Optional: Add foreign key constraint if you have a lawns table
            $table->foreign('lawn_id')->references('id')->on('lawns')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['lawn_id']);
            $table->dropColumn('lawn_id');
        });
    }
};
