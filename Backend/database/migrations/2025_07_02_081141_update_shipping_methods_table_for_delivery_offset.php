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
            // 1. Xóa cột 'expected_delivery_date' hiện có
            $table->dropColumn('expected_delivery_date');

            // 2. Thêm cột 'delivery_days_offset' mới
            // 'integer' để lưu số ngày (ví dụ: 1, 3, 5)
            // 'nullable()' để cho phép nó có thể rỗng nếu một phương thức không có offset cụ thể
            // 'after('price')' để đặt cột này sau cột 'price' cho dễ nhìn
            $table->integer('delivery_days_offset')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            // Trong trường hợp rollback:
            // 1. Xóa cột 'delivery_days_offset'
            $table->dropColumn('delivery_days_offset');

            // 2. Thêm lại cột 'expected_delivery_date' đã bị xóa
            // Đặt 'nullable()' để phù hợp với trạng thái ban đầu của nó
            $table->date('expected_delivery_date')->nullable()->after('price');
        });
    }
};