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
        Schema::create('user_pppoe', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id'); // User yang membuat PPPoE secret
            $table->uuid('router_id'); // Router tempat secret dibuat
            $table->string('username'); // Username PPPoE
            $table->string('password'); // Password PPPoE
            $table->string('service')->default('pppoe');
            $table->string('profile')->nullable();
            $table->string('local_address')->nullable();
            $table->string('remote_address')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('disabled')->default(false);
            $table->string('mikrotik_id')->nullable(); // ID dari mikrotik
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('router_id')->references('id')->on('routers')->onDelete('cascade');
            
            $table->unique(['router_id', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_pppoe');
    }
};
