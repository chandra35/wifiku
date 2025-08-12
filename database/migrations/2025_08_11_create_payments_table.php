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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->date('billing_date'); // Tanggal tagihan (untuk bulan apa)
            $table->date('due_date'); // Tanggal jatuh tempo
            $table->date('paid_date')->nullable(); // Tanggal pembayaran
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('confirmed_by')->nullable(); // Admin yang konfirmasi pembayaran
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('confirmed_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['customer_id', 'billing_date']);
            $table->index(['due_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
