<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; // Don't forget to import Storage!

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_url',
        // 'order', // Keep if you added 'order' column in migration
    ];

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the full URL for the image.
     *
     * @param  string  $value
     * @return string
     */
    public function getImageUrlAttribute($value)
    {
        // Check if the stored value is already a full URL (e.g., from seeder or external source)
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // If it's a storage path, return the full URL using Storage facade
        // This assumes you've run 'php artisan storage:link'
        return Storage::url($value);
    }

    // Optional: If you added an 'order' column to your migration,
    // you might want to cast it to integer
    // protected $casts = [
    //     'order' => 'integer',
    // ];
}