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
        'expires_at',
        'start_date',
        'end_date',
        'min_order_amount',
        'max_discount',
        'usage_limit',   
        'per_user_limit',
    ];

    protected $dates = ['expires_at', 'start_date', 'end_date', 'deleted_at']; // Thêm deleted_at
}
