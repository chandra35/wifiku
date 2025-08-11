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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('customer_id')->unique(); // ID Pelanggan unik (CUS202508XXXX)
            
            // Data Pribadi
            $table->string('name'); // Nama Lengkap
            $table->string('phone', 20); // No HP (required)
            $table->string('email')->nullable(); // Email
            $table->string('identity_number', 16)->nullable(); // NIK
            $table->date('birth_date')->nullable(); // Tanggal Lahir
            $table->enum('gender', ['male', 'female'])->nullable(); // Jenis Kelamin
            
            // Alamat
            $table->text('address'); // Alamat detail (required)
            $table->string('postal_code', 10)->nullable(); // Kode Pos
            
            // Indonesia Location (Laravolt)
            $table->char('province_id', 2)->nullable();
            $table->char('city_id', 4)->nullable();
            $table->char('district_id', 7)->nullable();
            $table->char('village_id', 10)->nullable();
            
            // Package & Service Information
            $table->uuid('package_id'); // ID Paket (required)
            $table->date('installation_date')->nullable(); // Tanggal Pasang
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi-annual', 'annual'])->default('monthly'); // Siklus tagihan
            $table->date('next_billing_date')->nullable(); // Tanggal tagihan berikutnya
            
            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->text('notes')->nullable(); // Catatan
            
            // PPPoE Secret Relations (bisa kosong jika belum dibuat)
            $table->uuid('pppoe_secret_id')->nullable(); // ID PPPoE Secret
            
            // User Relations
            $table->uuid('created_by')->nullable(); // Admin yang mendaftarkan
            $table->uuid('updated_by')->nullable(); // Admin yang terakhir update
            
            $table->timestamps();
            
            // Foreign Keys (tanpa pppoe_secret_id dulu)
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Laravolt Foreign Keys (optional, akan dikomentar jika tidak ada)
            // $table->foreign('province_id')->references('id')->on('provinces')->onDelete('set null');
            // $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            // $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            // $table->foreign('village_id')->references('id')->on('villages')->onDelete('set null');
            
            // Indexes
            $table->index(['status', 'package_id']);
            $table->index(['created_at', 'status']);
            $table->index('phone');
            $table->index('email');
            $table->index('next_billing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
