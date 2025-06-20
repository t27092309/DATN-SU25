<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'gender',
        'price',
        'category_id',
        'brand_id',
        'views'
    ];





    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class); // Đảm bảo bạn đã import App\Models\Brand
    }
    public function usageProfile()
    {
        return $this->hasOne(ProductUsageProfile::class);
    }

    public function scentGroups()
    {
        return $this->belongsToMany(ScentGroup::class, 'product_scent_profiles')
            ->withPivot('strength')
            ->withTimestamps();
    }

    public function scentProfiles()
    {
        return $this->hasMany(ProductScentProfile::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
