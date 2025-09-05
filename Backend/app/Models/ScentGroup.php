<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScentGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'color_code',
        'is_active',
    ];

    protected $dates = ['deleted_at'];

    public function productScentProfiles()
    {
        return $this->hasMany(ProductScentProfile::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_scent_profiles')
            ->where('scent_groups.is_active', 1) // chỉ lấy active
            ->withPivot('strength')
            ->withTimestamps();
    }
    public function scentProfiles()
    {
        return $this->hasMany(ProductScentProfile::class)
            ->whereHas('scentGroup', function ($q) {
                $q->where('is_active', 1); // chỉ lấy nhóm hương đang bật
            });
    }

}
