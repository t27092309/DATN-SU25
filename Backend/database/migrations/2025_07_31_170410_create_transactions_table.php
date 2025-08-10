<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // khóa ngoại
            $table->string('payment_method'); // 'momo', 'vnpay', 'fake'
            $table->unsignedBigInteger('amount'); // số tiền
            $table->string('status')->default('pending'); // pending, success, failed
            $table->string('payment_type')->nullable(); // loại thanh toán (ATM, QR...)
            $table->string('payment_id')->nullable(); // mã giao dịch ngân hàng trả về
            $table->text('message')->nullable(); // thông báo mô tả kết quả
            $table->text('extra_data')->nullable(); // dữ liệu bổ sung nếu cần
            $table->timestamps();

            // Khóa ngoại liên kết với orders
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
