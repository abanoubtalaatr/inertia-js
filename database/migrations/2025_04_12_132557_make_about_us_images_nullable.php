<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('about_us_sections', function (Blueprint $table) {
            $table->string('image_path1')->nullable()->change();
            $table->string('image_path2')->nullable()->change();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('about_us_sections', function (Blueprint $table) {
            //
        });
    }
};