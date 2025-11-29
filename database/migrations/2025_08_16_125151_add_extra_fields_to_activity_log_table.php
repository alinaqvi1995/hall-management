<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('batch_uuid');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->json('location')->nullable()->after('user_agent');
            $table->json('old_values')->nullable()->after('location');
            $table->json('new_values')->nullable()->after('old_values');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'location',
                'old_values',
                'new_values',
            ]);
        });
    }
};
