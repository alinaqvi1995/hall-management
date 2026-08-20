<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Drops tables carried over from an unrelated "service site" project.
 * Nothing in the hall-management domain references them.
 */
return new class extends Migration
{
    public function up(): void
    {
        // These tables reference each other; drop without ordering constraints.
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('subcategories');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('portfolios');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally irreversible: these tables belong to a different project.
    }
};
