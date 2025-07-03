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

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'transaction_id',
        'payer_id',
        'payment_status',
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