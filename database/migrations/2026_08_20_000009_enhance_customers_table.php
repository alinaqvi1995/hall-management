<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('secondary_phone', 30)->nullable()->after('phone');
            $table->boolean('is_blacklisted')->default(false)->after('address');
            $table->text('blacklist_reason')->nullable()->after('is_blacklisted');
            $table->softDeletes();

            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone']);
            $table->dropSoftDeletes();
            $table->dropColumn(['secondary_phone', 'is_blacklisted', 'blacklist_reason']);
        });
    }
};
