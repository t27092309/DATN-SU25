<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'is_active',
        'delivery_time_unit',
        'delivery_time_min',
        'delivery_time_max',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean', //Đảm bảo luôn là boolean
        'delivery_time_min' => 'integer',
        'delivery_time_max' => 'integer',
    ];
}
