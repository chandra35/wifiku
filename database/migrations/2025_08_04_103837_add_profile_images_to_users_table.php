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
            $table->string('pic_photo')->nullable()->after('email_verified_at');
            $table->string('isp_logo')->nullable()->after('pic_photo');
            $table->string('company_name')->nullable()->after('isp_logo');
            $table->text('company_address')->nullable()->after('company_name');
            $table->string('company_phone')->nullable()->after('company_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pic_photo', 'isp_logo', 'company_name', 'company_address', 'company_phone']);
        });
    }
};
