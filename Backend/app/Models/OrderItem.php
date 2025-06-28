<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
    'order_id',
    'product_variant_id',
    'quantity',
    'price_each',
    'variant_sku',
    'variant_name',
    'variant_status',
    'variant_description',
];
}
