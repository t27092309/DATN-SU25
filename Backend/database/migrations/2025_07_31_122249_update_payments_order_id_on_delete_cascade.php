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
            // First, drop the existing foreign key constraint.
            // The default naming convention is `table_column_foreign`.
            $table->dropForeign(['order_id']);

            // Now, add the new foreign key constraint with the onDelete('cascade') behavior.
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('cascade');
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
