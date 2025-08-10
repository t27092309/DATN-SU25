<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Thêm dòng này

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thay thế ['value1', 'value2', 'value3', 'value4'] bằng các ENUM hiện có của bạn
        // và thêm 'pending_payment', 'payment_failed', v.v.
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'pending_payment', 'processing', 'shipped', 'delivered', 'cancelled', 'payment_failed', 'refunded') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tùy chọn: Để hoàn tác nếu cần rollback.
        // Đặt lại về trạng thái ban đầu hoặc trạng thái mong muốn khi rollback
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending'");
    }
};