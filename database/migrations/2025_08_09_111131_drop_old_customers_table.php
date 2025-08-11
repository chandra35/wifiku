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
        // Drop existing customers table if exists
        Schema::dropIfExists('customers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't easily recreate the old table structure
        // This migration is one-way only for cleanup
    }
};
