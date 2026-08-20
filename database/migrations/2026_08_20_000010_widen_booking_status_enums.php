<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the states the workflow actually needs:
 *  - status: `completed` for events that have finished and been settled.
 *  - payment_status: `refunded` for cancelled bookings whose money went back.
 *
 * Raw ALTERs because Laravel cannot modify an enum's members portably.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only MySQL/MariaDB has a real ENUM type to alter. On other drivers the
        // create migration already declares the full value set.
        if (! $this->isMySql()) {
            return;
        }

        DB::statement("ALTER TABLE `bookings` MODIFY `status`
            ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `bookings` MODIFY `payment_status`
            ENUM('pending','partial','paid','refunded') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! $this->isMySql()) {
            return;
        }

        // Collapse the new states back onto the nearest legacy value first, so
        // the narrower column definition can be applied without truncation.
        DB::table('bookings')->where('status', 'completed')->update(['status' => 'confirmed']);
        DB::table('bookings')->where('payment_status', 'refunded')->update(['payment_status' => 'pending']);

        DB::statement("ALTER TABLE `bookings` MODIFY `status`
            ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending'");

        DB::statement("ALTER TABLE `bookings` MODIFY `payment_status`
            ENUM('pending','partial','paid') NOT NULL DEFAULT 'pending'");
    }

    private function isMySql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
