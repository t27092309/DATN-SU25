<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductScentProfile extends Model
{
    use HasFactory;
    
     protected $table = 'product_scent_profiles'; // Ensure this matches your migration table name

    // Relationship to ScentGroup
    public function scentGroup()
    {
        return $this->belongsTo(ScentGroup::class);
    }

    // Relationship to Product (optional, depending on your needs)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
