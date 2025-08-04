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
            $table->unsignedBigInteger('province_id')->nullable()->after('company_phone');
            $table->unsignedBigInteger('city_id')->nullable()->after('province_id');
            $table->unsignedBigInteger('district_id')->nullable()->after('city_id');
            $table->unsignedBigInteger('village_id')->nullable()->after('district_id');
            $table->text('full_address')->nullable()->after('village_id');
            
            // Foreign key constraints
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
            
            $table->dropColumn(['province_id', 'city_id', 'district_id', 'village_id', 'full_address']);
        });
    }
};
