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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->constrained()->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('price_each', 10, 2); // Giá tại thời điểm đặt

            // Snapshot thông tin biến thể tại thời điểm đặt
            $table->string('variant_sku')->nullable();
            $table->string('variant_name')->nullable(); // VD: "Dior Sauvage EDP"
            $table->enum('variant_status', ['available', 'out_of_stock', 'discontinued'])->nullable();
            $table->text('variant_description')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
