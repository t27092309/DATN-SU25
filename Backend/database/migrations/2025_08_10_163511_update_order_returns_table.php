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
        Schema::table('order_returns', function (Blueprint $table) {
            // Bước 1: Cập nhật cột 'status'
            // Đặt lại kiểu enum với các giá trị mới
            $table->enum('status', ['requested', 'approved', 'rejected', 'returned', 'refunded'])
                  ->default('requested')
                  ->change();

            // Bước 2: Thêm các cột mới
            $table->text('notes')->nullable()->after('status');
            $table->foreignId('processed_by')->nullable()->constrained('users')->after('notes');
            $table->timestamp('processed_at')->nullable()->after('processed_by');
            
            // Bước 3: Cập nhật ràng buộc khóa ngoại
            $table->dropForeign(['order_id']);
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
        Schema::table('order_returns', function (Blueprint $table) {
            // Xóa các cột đã thêm
            $table->dropColumn(['notes', 'processed_by', 'processed_at']);

            // Khôi phục lại cột 'status' cũ
            $table->enum('status', ['requested', 'approved', 'rejected', 'processed'])
                  ->default('requested')
                  ->change();
                  
            // Khôi phục ràng buộc khóa ngoại cũ
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders');
        });
    }
};