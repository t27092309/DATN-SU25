<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thay đổi cột status để thêm các giá trị 'return_requested' và 'refunded'
        DB::statement("ALTER TABLE orders CHANGE status status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'return_requested', 'refunded', 'pending_payment', 'payment_failed', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Đảo ngược lại thay đổi trong down()
        // Trả về trạng thái ENUM ban đầu (tùy thuộc vào các trạng thái cũ của bạn)
        // Bạn có thể chỉnh sửa lại danh sách ENUM này nếu có các trạng thái khác
        DB::statement("ALTER TABLE orders CHANGE status status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'return_requested', 'refunded', 'pending_payment', 'payment_failed') DEFAULT 'pending'");
    }
};
