<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Transaction extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'payment_type',      // 'vnpay' hoặc 'momo'
        'payment_id',        // Mã giao dịch trả về từ MoMo/VNPAY
        'message',           // Thông báo kết quả
        'extra_data',        // Lưu thêm (JSON, nếu cần)
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
