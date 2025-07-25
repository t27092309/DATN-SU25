<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path', 
        'order',
    ];

    // Append 'full_url' to the model's array/JSON representation
    protected $appends = ['full_url'];

    // Cast 'order' if it's an integer column
    protected $casts = [
        'order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessor for the full URL of the image.
     * This method name directly corresponds to the 'full_url' appended attribute.
     */
    public function getFullUrlAttribute()
    {
        // If the stored 'path' value is already a full URL (e.g., from seeder or external source)
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }

        // If it's a storage path, return the full URL using Storage facade
        return Storage::url($this->path);
    }

    // Optional: Override delete method to remove file from storage
    // This is good practice to clean up files when a record is deleted.
    protected static function booted()
    {
        static::deleting(function ($image) {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
}