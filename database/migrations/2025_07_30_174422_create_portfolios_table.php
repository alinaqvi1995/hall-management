<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('image')->nullable(); // Thumbnail or banner
            $table->text('short_description')->nullable(); // For card/listing view
            $table->longText('description')->nullable(); // Full description for detail view
            $table->string('client_name')->nullable(); // Optional: name of the client
            $table->string('project_url')->nullable(); // Optional: external link
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
