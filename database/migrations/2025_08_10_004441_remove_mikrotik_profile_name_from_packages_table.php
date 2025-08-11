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
            // Drop column if exists
            if (Schema::hasColumn('packages', 'mikrotik_profile_name')) {
                $table->dropColumn('mikrotik_profile_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('mikrotik_profile_name')->nullable();
            $table->index('mikrotik_profile_name');
        });
    }
};
