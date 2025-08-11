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
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Nama Paket
            $table->text('description')->nullable(); // Deskripsi Paket
            
            // Kecepatan (sama seperti PPP Profile)
            $table->string('rate_limit')->nullable(); // rx-rate[/tx-rate] [rx-burst-rate[/tx-burst-rate] [rx-burst-threshold[/tx-burst-threshold] [rx-burst-time[/tx-burst-time]]]]
            $table->string('burst_limit')->nullable(); // Burst limit terpisah untuk kemudahan
            $table->string('burst_threshold')->nullable(); // Burst threshold
            $table->string('burst_time')->nullable(); // Burst time
            $table->string('local_address')->nullable(); // Pool IP Address
            $table->string('remote_address')->nullable(); // Gateway
            $table->string('dns_server')->nullable(); // DNS Server
            $table->boolean('only_one')->default(false); // Only One session
            $table->integer('session_timeout')->nullable(); // Session timeout (seconds)
            $table->integer('idle_timeout')->nullable(); // Idle timeout (seconds)
            $table->text('address_list')->nullable(); // Address list
            
            // Harga & Billing
            $table->decimal('price', 15, 2); // Harga paket
            $table->string('currency', 3)->default('IDR'); // Mata uang
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi-annual', 'annual'])->default('monthly'); // Siklus tagihan
            
            // PPP Profile Relations
            $table->uuid('router_id'); // Router yang digunakan
            $table->string('mikrotik_profile_name')->nullable(); // Nama profile di MikroTik
            
            // Status & Meta
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Urutan tampilan
            $table->uuid('created_by')->nullable(); // Dibuat oleh
            $table->uuid('updated_by')->nullable(); // Diperbarui oleh
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('router_id')->references('id')->on('routers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['router_id', 'is_active']);
            $table->index('mikrotik_profile_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
