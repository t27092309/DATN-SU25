<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

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
        return $this->belongsTo(Brand::class);
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
        public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
        public function getImageUrlAttribute($value)
    {
        // Check if the stored value is already a full URL (e.g., from seeder or external source)
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // If it's a storage path, return the full URL using Storage facade
        // This assumes you've run 'php artisan storage:link'
        return $value ? Storage::url($value) : null;
    }
}
