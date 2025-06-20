<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScentGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'color_code'
    ];
    
    public function productScentProfiles()
    {
        return $this->hasMany(ProductScentProfile::class);
    }

    // Direct many-to-many with Product through the pivot table
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_scent_profiles')
                    ->withPivot('strength')
                    ->withTimestamps();
    }
}
