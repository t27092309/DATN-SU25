<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUsageProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'spring_percent',
        'summer_percent',
        'autumn_percent',
        'winter_percent',
        'suitable_day',
        'suitable_night',
        'longevity_hours',
        'sillage_range_m',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}