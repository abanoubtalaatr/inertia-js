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
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('preferred_language')->default('ar');
            $table->enum('role', ['user', 'specialist', 'company', 'admin'])->default('user');
            $table->boolean('verified')->default(false);
            $table->string('profile_picture')->nullable();
            $table->string('specialization')->nullable();
            $table->string('qualification')->nullable();
            $table->integer('experience_years')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->json('verification_documents')->nullable();
            $table->json('available_days')->nullable();
            $table->json('available_hours')->nullable();
            $table->string('commercial_register')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'preferred_language', 'role', 'status', 'verified',
                'profile_picture', 'specialization', 'qualification', 'experience_years', 'bio', 'company_id',
                'verification_documents', 'available_days', 'available_hours', 'commercial_register', 'tax_number',
                'description', 'logo',
            ]);

            // Drop foreign keys
            $table->dropForeign(['company_id']);
        });
    }
};
