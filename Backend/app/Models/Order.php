<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'notes',
        'coupon_id',
        'payment_method',
        'shipping_fee',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
    ];

    /**
     * Get the user that owns the order.
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

    /**
     * Get the payment associated with the order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class); // Mối quan hệ 1-1
    }

    /**
     * Get the coupon that was applied to the order.
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}