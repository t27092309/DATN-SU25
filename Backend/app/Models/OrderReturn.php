<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'order_returns';

    protected $fillable = [
        'order_id',
        'reason',
        'notes',
        'status',
        'processed_by',
        'processed_at',
    ];

    /**
     * Mối quan hệ với đơn hàng.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Mối quan hệ với người dùng đã xử lý yêu cầu.
     * Thêm mối quan hệ này để lấy được tên người duyệt.
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}