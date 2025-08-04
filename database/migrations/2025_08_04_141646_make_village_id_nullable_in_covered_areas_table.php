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
        Schema::table('covered_areas', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['village_id']);
            
            // Drop unique constraint that includes village_id
            $table->dropUnique('unique_user_coverage_area');
            
            // Make village_id nullable
            $table->unsignedBigInteger('village_id')->nullable()->change();
            
            // Re-add foreign key constraint with nullable
            $table->foreign('village_id')->references('id')->on('indonesia_villages')->onDelete('cascade');
            
            // Re-add unique constraint but without village_id since it can be null
            // We'll handle uniqueness in application logic instead
            $table->index(['user_id', 'province_id', 'city_id', 'district_id', 'village_id'], 'coverage_area_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covered_areas', function (Blueprint $table) {
            // Drop the index
            $table->dropIndex('coverage_area_index');
            
            // Drop foreign key constraint
            $table->dropForeign(['village_id']);
            
            // Make village_id non-nullable again
            $table->unsignedBigInteger('village_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('village_id')->references('id')->on('indonesia_villages')->onDelete('cascade');
            
            // Re-add unique constraint
            $table->unique(['user_id', 'province_id', 'city_id', 'district_id', 'village_id'], 'unique_user_coverage_area');
        });
    }
};
