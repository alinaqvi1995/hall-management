<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Change excerpt from VARCHAR(255) → LONGTEXT
            $table->longText('excerpt')->change();
        });
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            // Revert back to string (VARCHAR 255)
            $table->string('excerpt', 255)->nullable()->change();
        });
    }
};
