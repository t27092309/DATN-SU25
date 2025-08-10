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
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->string('delivery_time_unit')->nullable()->comment('Unit for delivery_time: "hours" or "days"');
            $table->integer('delivery_time_min')->nullable()->comment('Minimum delivery time (in hours or days, based on unit)');
            $table->integer('delivery_time_max')->nullable()->comment('Maximum delivery time (in hours or days, based on unit)');
            $table->dropColumn('delivery_days_offset');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            // Đảo ngược các thay đổi trong up()
            $table->dropColumn(['delivery_time_unit', 'delivery_time_min', 'delivery_time_max']);
            $table->integer('delivery_days_offset')->nullable();
        });
    }
};
