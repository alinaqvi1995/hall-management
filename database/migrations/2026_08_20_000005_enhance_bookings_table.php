<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('event_type', 50)->nullable()->after('customer_id');
            $table->foreignId('package_id')->nullable()->after('lawn_id')
                ->constrained('packages')->nullOnDelete();

            // Pricing breakdown. `quote_price` stays as the owner's opening quote;
            // these columns hold the computed bill.
            $table->unsignedInteger('guest_count')->nullable()->after('capacity');
            $table->decimal('per_head_rate', 10, 2)->default(0)->after('guest_count');
            $table->decimal('menu_amount', 12, 2)->default(0)->after('per_head_rate');
            $table->decimal('addons_amount', 12, 2)->default(0)->after('menu_amount');
            $table->decimal('hall_rent', 12, 2)->default(0)->after('addons_amount');
            $table->decimal('discount', 12, 2)->default(0)->after('hall_rent');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('discount');
            $table->decimal('tax_amount', 12, 2)->default(0)->after('tax_percent');
            $table->decimal('total_amount', 12, 2)->default(0)->after('tax_amount');

            // Cancellation trail.
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->decimal('cancellation_charge', 12, 2)->default(0)->after('cancellation_reason');
            $table->foreignId('cancelled_by')->nullable()->after('cancellation_charge')
                ->constrained('users')->nullOnDelete();

            // Overlap checks filter on these three columns on every availability
            // probe and calendar render.
            $table->index(['lawn_id', 'start_datetime', 'end_datetime'], 'bookings_lawn_window_idx');
            $table->index(['hall_id', 'status'], 'bookings_hall_status_idx');
            $table->index('start_datetime', 'bookings_start_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_lawn_window_idx');
            $table->dropIndex('bookings_hall_status_idx');
            $table->dropIndex('bookings_start_idx');
            $table->dropForeign(['package_id']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'event_type', 'package_id', 'guest_count', 'per_head_rate',
                'menu_amount', 'addons_amount', 'hall_rent', 'discount',
                'tax_percent', 'tax_amount', 'total_amount',
                'cancelled_at', 'cancellation_reason', 'cancellation_charge', 'cancelled_by',
            ]);
        });
    }
};
