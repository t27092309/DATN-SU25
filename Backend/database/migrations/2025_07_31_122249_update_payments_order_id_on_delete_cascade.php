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
        Schema::table('payments', function (Blueprint $table) {
            // Since the foreign key constraint doesn't exist yet, we don't need to drop it.
            // We just add it directly with onDelete('cascade').
            // The column 'order_id' already exists (from your initial payments migration),
            // so we use foreign() instead of foreignId().
            $table->foreign('order_id')
                  ->references('id')->on('orders')
                  ->onDelete('cascade'); // Add the cascade behavior
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // When rolling back, we need to drop the foreign key we just added.
            // Laravel's default naming convention for this constraint would be 'payments_order_id_foreign'.
            $table->dropForeign(['order_id']);
        });
    }
};