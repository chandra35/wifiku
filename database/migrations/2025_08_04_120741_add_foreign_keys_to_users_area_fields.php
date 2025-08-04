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
            // Foreign key constraints dengan prefix table
            $table->foreign('province_id')->references('id')->on('indonesia_provinces')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('indonesia_cities')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('indonesia_districts')->onDelete('set null');
            $table->foreign('village_id')->references('id')->on('indonesia_villages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['village_id']);
        });
    }
};
