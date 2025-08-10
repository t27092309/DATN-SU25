<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Make sure to use DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, modifying ENUMs typically requires changing the column definition
        DB::statement("ALTER TABLE payments CHANGE payment_status payment_status ENUM('pending', 'paid', 'failed', 'refunded', 'cancelled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // When rolling back, remove 'cancelled' from the ENUM
        DB::statement("ALTER TABLE payments CHANGE payment_status payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");
    }
};