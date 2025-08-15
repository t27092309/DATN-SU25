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
        // Sử dụng Schema::table để thay đổi bảng 'orders'
        Schema::table('orders', function (Blueprint $table) {
            // Cập nhật cột 'status' với các giá trị ENUM mới
            // Loại bỏ 'refunded' và thay thế bằng 'delivered'
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'payment_failed'])
                  ->default('pending')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại trạng thái cũ cho cột 'status'
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'payment_failed'])
                  ->default('pending')
                  ->change();
        });
    }
};