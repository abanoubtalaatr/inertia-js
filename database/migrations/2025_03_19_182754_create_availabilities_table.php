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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('day_of_week'); // e.g., monday, tuesday
            $table->time('start_time')->nullable(); // e.g., 08:00
            $table->time('end_time')->nullable(); // e.g., 18:00
            $table->boolean('is_off')->default(false); // True if the day is off
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
