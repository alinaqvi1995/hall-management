<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();

            // Main content structure
            $table->string('heading_one')->nullable();
            $table->longText('description_one')->nullable();
            $table->string('image_one')->nullable();

            $table->string('heading_two')->nullable();
            $table->longText('description_two')->nullable();
            $table->string('image_two')->nullable();

            // Additional descriptive sections
            $table->longText('description_two_one')->nullable();
            $table->longText('description_two_two')->nullable();
            $table->longText('description_two_three')->nullable();
            $table->longText('description_two_four')->nullable();
            $table->longText('description_two_five')->nullable();
            $table->longText('description_two_six')->nullable();
            $table->longText('description_two_seven')->nullable();

            $table->string('heading_three')->nullable();
            $table->longText('description_three')->nullable();
            $table->string('image_three')->nullable();
            $table->string('image_four')->nullable();

            // Meta & SEO
            $table->string('slug')->unique();
            $table->string('excerpt')->nullable();
            $table->string('tags')->nullable();
            $table->string('title')->nullable();

            // Author & Attribution
            $table->string('author')->nullable();
            $table->longText('author_note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            // Category & Subcategory
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('subcategory_id')->nullable();

            // Extra fields
            $table->string('header_image_btn')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('views')->default(0);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
