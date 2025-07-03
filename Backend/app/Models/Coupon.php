<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Thêm dòng này

class Coupon extends Model
{
    use HasFactory, SoftDeletes; // Thêm SoftDeletes

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'usage_limit',
        'used_count',
        'per_user_limit',
        'min_order_amount',
        'max_discount',
        'usage_limit',   
        'per_user_limit',

    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'per_user_limit' => 'integer',
        'is_active' => 'boolean',
        // --- THÊM DÒNG NÀY ĐỂ KHẮC PHỤC LỖI ---
        'start_date' => 'datetime', // Cast start_date sang Carbon object
        'end_date' => 'datetime',   // Cast end_date sang Carbon object
    ];
}
