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
        Schema::create('covered_areas', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Foreign key to users table
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('village_id');
            $table->text('description')->nullable(); // Deskripsi area coverage
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('indonesia_provinces')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('indonesia_cities')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('indonesia_districts')->onDelete('cascade');
            $table->foreign('village_id')->references('id')->on('indonesia_villages')->onDelete('cascade');

            // Unique constraint untuk mencegah duplikasi area per user
            $table->unique(['user_id', 'province_id', 'city_id', 'district_id', 'village_id'], 'unique_user_coverage_area');

            // Index untuk performa query
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('covered_areas');
    }
};
