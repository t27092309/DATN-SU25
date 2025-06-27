<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'stock',
        'sold',
        'status',
        'barcode',
        'description'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attribute_value', // Tên bảng pivot
            'product_variant_id',              // Khóa ngoại của ProductVariant trên bảng pivot
            'attribute_value_id'               // Khóa ngoại của AttributeValue trên bảng pivot
        );
    }
}
