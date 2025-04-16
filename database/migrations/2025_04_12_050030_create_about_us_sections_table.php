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
        Schema::create('about_us_sections', function (Blueprint $table) {
            $table->id();
            $table->string('image_path1');
            $table->string('image_path2');
            $table->timestamps();
        });

        Schema::create('about_us_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_us_section_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us_sections');
    }
};