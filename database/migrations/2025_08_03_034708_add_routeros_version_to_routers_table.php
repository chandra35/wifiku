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
        Schema::table('routers', function (Blueprint $table) {
            $table->string('routeros_version')->nullable()->after('password');
            $table->string('architecture')->nullable()->after('routeros_version');
            $table->string('board_name')->nullable()->after('architecture');
            $table->timestamp('last_system_check')->nullable()->after('board_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            $table->dropColumn(['routeros_version', 'architecture', 'board_name', 'last_system_check']);
        });
    }
};
