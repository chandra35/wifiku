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
            // Change id to uuid and add role_id
            $table->dropColumn('id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->first();
            $table->uuid('role_id')->nullable()->after('remember_token');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id']);
            $table->dropColumn('id');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->id()->first();
        });
    }
};
