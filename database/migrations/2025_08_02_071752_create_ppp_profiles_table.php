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
        Schema::create('ppp_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('router_id');
            $table->string('local_address')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('dns_server')->nullable();
            $table->string('rate_limit')->nullable();
            $table->integer('session_timeout')->nullable();
            $table->integer('idle_timeout')->nullable();
            $table->boolean('only_one')->default(false);
            $table->text('comment')->nullable();
            $table->string('mikrotik_id')->nullable();
            $table->uuid('created_by');
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['name', 'router_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_profiles');
    }
};
