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

    protected $casts = [
        'price_each' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the order that the order item belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product variant associated with the order item.
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}