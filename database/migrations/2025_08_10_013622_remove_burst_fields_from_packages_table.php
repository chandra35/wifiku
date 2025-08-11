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
        Schema::table('packages', function (Blueprint $table) {
            // Drop burst-related columns if they exist
            if (Schema::hasColumn('packages', 'burst_limit')) {
                $table->dropColumn('burst_limit');
            }
            if (Schema::hasColumn('packages', 'burst_threshold')) {
                $table->dropColumn('burst_threshold');
            }
            if (Schema::hasColumn('packages', 'burst_time')) {
                $table->dropColumn('burst_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('burst_limit')->nullable();
            $table->string('burst_threshold')->nullable();
            $table->string('burst_time')->nullable();
        });
    }
};
