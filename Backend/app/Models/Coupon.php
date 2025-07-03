<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon; // Đảm bảo import Carbon

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',  
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'is_active', 
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Kiểm tra xem coupon có còn hoạt động (active) không.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Kiểm tra xem coupon đã hết hạn chưa.
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Kiểm tra xem coupon đã đến ngày bắt đầu sử dụng chưa.
     * @return bool
     */
    public function isYetToStart(): bool
    {
        return $this->start_date && $this->start_date->isFuture();
    }

    /**
     * Kiểm tra xem coupon đã đạt giới hạn sử dụng tổng cộng chưa.
     * @return bool
     */
    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    /**
     * Kiểm tra xem coupon có yêu cầu số tiền tối thiểu không.
     * @param float $amount
     * @return bool
     */
    public function meetsMinOrderAmount(float $amount): bool
    {
        return $this->min_order_amount === null || $amount >= $this->min_order_amount;
    }

    /**
     * Tính toán giá trị giảm giá dựa trên tổng đơn hàng.
     * @param float $totalOrderAmount
     * @return float
     */
    public function calculateDiscount(float $totalOrderAmount): float
    {
        $discount = 0;
        if ($this->discount_type === 'percent') {
            $discount = ($totalOrderAmount * $this->discount_value / 100);
        } else { // fixed
            $discount = $this->discount_value;
        }

        // Áp dụng giới hạn giảm giá tối đa
        if ($this->max_discount !== null && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        return $discount;
    }
}