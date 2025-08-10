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
    Schema::create('coupons', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->enum('discount_type', ['percent', 'fixed']);
        $table->decimal('discount_value', 10, 2);
        $table->timestamp('expires_at')->nullable();
        $table->date('start_date')->nullable();           // Ngày bắt đầu
        $table->date('end_date')->nullable();             // Ngày kết thúc
        $table->decimal('min_order_amount', 12, 2)->nullable(); // Điều kiện giá trị đơn hàng tối thiểu
        $table->decimal('max_discount', 12, 2)->nullable();     // Giảm tối đa
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
