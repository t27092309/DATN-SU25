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
            // 1. Thêm cột 'amount'
            // Đặt sau 'payment_method' để giữ cấu trúc logic
            $table->decimal('amount', 10, 2)->after('payment_method');

            // 2. Thêm cột 'transaction_id'
            $table->string('transaction_id')->nullable()->unique()->after('amount');

            // 3. Thêm cột 'payer_id' (tùy chọn, nếu cần theo dõi người trả tiền trên cổng)
            $table->string('payer_id')->nullable()->after('transaction_id');

            // 4. Sửa đổi cột 'payment_status' để thêm trạng thái 'refunded'
            // Phương thức 'change()' yêu cầu bạn phải cài đặt package 'doctrine/dbal'
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->change();

            // 5. Thêm cột 'payment_details' để lưu JSON data
            $table->json('payment_details')->nullable()->after('paid_at');

            // 6. Cập nhật khóa ngoại để bao gồm onDelete('cascade')
            // Nếu bạn đã có khóa ngoại này từ migration 'create_payments_table',
            // bạn cần bỏ comment và chạy dòng sau ĐỂ XÓA VÀ TẠO LẠI KHÓA NGOẠI
            // HOẶC bạn có thể bỏ qua bước này nếu bạn đã xử lý logic xóa đơn hàng bằng code.
            // Nếu bạn CHƯA chạy migration tạo bảng payments với onDelete('cascade'), bạn có thể bỏ comment dòng dưới.
            // $table->dropForeign(['order_id']);
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Đảo ngược các thay đổi theo thứ tự ngược lại
            $table->dropColumn('payment_details');
            $table->dropColumn('payer_id');
            $table->dropColumn('transaction_id');
            $table->dropColumn('amount');

            // Đảo ngược sửa đổi cột 'payment_status'
            // Lưu ý: Việc đảo ngược enum có thể phức tạp nếu có dữ liệu với trạng thái 'refunded'.
            // Nếu không có dữ liệu 'refunded', bạn có thể đưa về enum cũ.
            // Nếu có, bạn cần xử lý dữ liệu trước (ví dụ: chuyển về 'failed').
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->change();

            // Đảo ngược khóa ngoại nếu bạn đã thay đổi nó ở hàm up
            // $table->dropForeign(['order_id']);
            // $table->foreign('order_id')->references('id')->on('orders'); // Quay về trạng thái ban đầu (không có cascade)
        });
    }
};