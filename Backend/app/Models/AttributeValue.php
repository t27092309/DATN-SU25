<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    /**
     * Tên bảng liên kết với model.
     *
     * @var string
     */
    protected $table = 'attribute_values';

    /**
     * Các thuộc tính có thể được gán hàng loạt (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attribute_id',
        'value',
    ];

    /**
     * Định nghĩa mối quan hệ: Một AttributeValue thuộc về một Attribute.
     */
    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attribute_value', 'attribute_value_id', 'product_variant_id');
    }
}
