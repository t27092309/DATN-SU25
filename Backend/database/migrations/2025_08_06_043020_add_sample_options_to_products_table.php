<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('can_buy_sample')->default(false)->after('has_variants');
            $table->decimal('sample_price_increase', 10, 2)->nullable()->after('can_buy_sample');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('can_buy_sample');
            $table->dropColumn('sample_price_increase');
        });
    }
};
