<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Định nghĩa các hằng số cho trạng thái đơn hàng
    public const STATUS_PENDING = 'pending'; // Chờ xử lý (có thể chờ thanh toán hoặc chờ xác nhận)
    public const STATUS_PENDING_PAYMENT = 'pending_payment'; // Đang chờ thanh toán (thanh toán online)
    public const STATUS_PAYMENT_FAILED = 'payment_failed'; // Thanh toán thất bại
    public const STATUS_PROCESSING = 'processing'; // Đang xử lý
    public const STATUS_SHIPPED = 'shipped';       // Đã giao hàng
    public const STATUS_DELIVERED = 'delivered';   // Đã giao thành công
    public const STATUS_CANCELLED = 'cancelled';   // Đã hủy
    public const STATUS_REFUNDED = 'refunded';     // Đã hoàn tiền

    // Có thể gom tất cả các trạng thái vào một mảng để dùng trong validation/enum
    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PENDING_PAYMENT,
        self::STATUS_PAYMENT_FAILED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    protected $fillable = [
        'user_id',
        'total_price',
        'status', // Vẫn giữ status ở đây
        'notes',
        'coupon_id',
        // 'payment_method', // <-- Đã xóa khỏi fillable
        'shipping_fee',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        // Thêm cast cho status nếu cần validation ở cấp độ model
        // 'status' => 'string', // Mặc định là string, không cần thiết
    ];

    // ... (Các mối quan hệ) ...

    /**
     * Get the payment(s) associated with the order.
     * Mối quan hệ should be hasMany if an order can have multiple payment attempts/refunds.
     * If an order strictly has only one payment record, hasOne is fine.
     * Consider if you need a history of payments (e.g., initial payment, then refund).
     * For now, hasOne is used as per your original code.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the order address for the order.
     */
    public function orderAddress()
    {
        return $this->hasOne(OrderAddress::class); // Mối quan hệ 1-1
    }

    public function payments() // Đổi tên thành payments để hợp lý hơn với hasMany
    {
        return $this->hasMany(Payment::class); // Đề xuất thay đổi thành hasMany
    }

    // Nếu bạn muốn giữ hasOne và chỉ có 1 bản ghi payment chính, bạn có thể giữ tên `payment()`
    public function primaryPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany(); // Lấy bản ghi thanh toán mới nhất
    }

    // Helper methods cho trạng thái
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPaymentFailed(): bool
    {
        return $this->status === self::STATUS_PAYMENT_FAILED;
    }
}
