<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            // Normalised location — the free-text city/state columns are kept for
            // backwards compatibility but these are now the source of truth.
            $table->unsignedBigInteger('state_id')->nullable()->after('address');
            $table->unsignedBigInteger('city_id')->nullable()->after('state_id');

            // Statutory identifiers that must appear on a Pakistani tax invoice.
            $table->string('ntn_number', 30)->nullable()->after('registration_number');
            $table->string('gst_number', 30)->nullable()->after('ntn_number');

            // Commercial defaults applied to new bookings for this hall.
            $table->decimal('default_per_head_rate', 10, 2)->nullable()->after('hall_capacity');
            $table->unsignedTinyInteger('advance_policy_percent')->default(25)->after('default_per_head_rate');
            $table->unsignedTinyInteger('cancellation_charge_percent')->default(0)->after('advance_policy_percent');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('cancellation_charge_percent');

            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('halls', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn([
                'state_id', 'city_id', 'ntn_number', 'gst_number',
                'default_per_head_rate', 'advance_policy_percent',
                'cancellation_charge_percent', 'tax_percent',
            ]);
        });
    }
};
