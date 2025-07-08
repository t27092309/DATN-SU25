<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Định nghĩa các phương thức thanh toán hợp lệ
    public const PAYMENT_METHODS = [
        'cash',
        'momo',
        'vnpay',
        // Thêm các phương thức khác nếu có
    ];

    // Định nghĩa các trạng thái thanh toán hợp lệ
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';
    public const PAYMENT_STATUS_CANCELLED = 'cancelled';

    public const ALL_PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PENDING,
        self::PAYMENT_STATUS_PAID,
        self::PAYMENT_STATUS_FAILED,
        self::PAYMENT_STATUS_REFUNDED,
        self::PAYMENT_STATUS_CANCELLED,
    ];

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'transaction_id',
        'payer_id',
        'payment_status', // Có thể thêm validation trong migration hoặc form request
        'paid_at',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_details' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
