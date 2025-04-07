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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialist_id')->references('id')->on('users');
            $table->foreignId('client_id')->references('id')->on('users');
            $table->date('date'); // e.g., 2025-03-24
            $table->time('start_time'); // e.g., 09:00
            $table->time('end_time'); // e.g., 09:30
            $table->enum('status', ['pending', 'confirmed', 'canceled', 'rescheduled', 'no-show', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
