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
        'color_code'
    ];

    protected $dates = ['deleted_at'];

    public function productScentProfiles()
    {
        return $this->hasMany(ProductScentProfile::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_scent_profiles')
                    ->withPivot('strength')
                    ->withTimestamps();
    }
}

