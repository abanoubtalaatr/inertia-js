<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvantagesTable extends Migration
{
    public function up()
    {
        // Advantages table
        Schema::create('advantages', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // Advantage translations table
        Schema::create('advantage_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advantage_id')->constrained('advantages')->onDelete('cascade');
            $table->string('locale')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unique(['advantage_id', 'locale']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('advantage_translations');
        Schema::dropIfExists('advantages');
    }
}
