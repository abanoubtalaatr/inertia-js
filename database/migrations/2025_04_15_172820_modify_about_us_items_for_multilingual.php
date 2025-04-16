<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First rename the existing columns
        Schema::table('about_us_items', function (Blueprint $table) {
            $table->renameColumn('title', 'title_en');
            $table->renameColumn('description', 'description_en');
        });

        // Then add columns for other languages
        Schema::table('about_us_items', function (Blueprint $table) {
            $table->string('title_ar')->nullable()->after('description_en');
            $table->text('description_ar')->nullable()->after('title_ar');
            $table->string('title_fr')->nullable()->after('description_ar');
            $table->text('description_fr')->nullable()->after('title_fr');
            $table->string('title_tl')->nullable()->after('description_fr');
            $table->text('description_tl')->nullable()->after('title_tl');
            $table->string('title_ur')->nullable()->after('description_tl');
            $table->text('description_ur')->nullable()->after('title_ur');
        });
    }

    public function down()
    {
        // First drop the added columns
        Schema::table('about_us_items', function (Blueprint $table) {
            $table->dropColumn([
                'title_ar', 'description_ar',
                'title_fr', 'description_fr',
                'title_tl', 'description_tl',
                'title_ur', 'description_ur'
            ]);
        });

        // Then rename back the original columns
        Schema::table('about_us_items', function (Blueprint $table) {
            $table->renameColumn('title_en', 'title');
            $table->renameColumn('description_en', 'description');
        });
    }
};
