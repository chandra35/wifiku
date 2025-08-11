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
            $table->uuid('ppp_profile_id')->nullable()->after('description');
            $table->decimal('price_before_tax', 15, 2)->nullable()->after('price');
            
            // Add foreign key constraint
            $table->foreign('ppp_profile_id')->references('id')->on('ppp_profiles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['ppp_profile_id']);
            $table->dropColumn(['ppp_profile_id', 'price_before_tax']);
        });
    }
};
