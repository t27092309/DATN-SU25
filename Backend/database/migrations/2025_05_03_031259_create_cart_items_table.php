<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');

            // Thêm product_id cho sản phẩm không có biến thể
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');

            // Giữ lại product_variant_id cho sản phẩm có biến thể
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('cascade');

            $table->integer('quantity');
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};


