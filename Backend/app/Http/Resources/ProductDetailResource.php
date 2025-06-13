<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    public function toArray($request)
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'image' => $this->image,
        'gender' => $this->gender,
        'price' => $this->price,
        'category_id' => $this->category_id,
        'brand_id' => $this->brand_id,
        'view' => $this->view,
        'description' => $this->description,

        'usage_profile' => $this->whenLoaded('usageProfile', function () {
            return [
                'spring_percent' => $this->usageProfile->spring_percent,
                'summer_percent' => $this->usageProfile->summer_percent,
                'autumn_percent' => $this->usageProfile->autumn_percent,
                'winter_percent' => $this->usageProfile->winter_percent,
                'suitable_day' => $this->usageProfile->suitable_day,
                'suitable_night' => $this->usageProfile->suitable_night,
                'longevity_hours' => $this->usageProfile->longevity_hours,
                'sillage_range_m' => $this->usageProfile->sillage_range_m,
            ];
        }),

        'scent_profiles' => $this->whenLoaded('scentProfiles', function () {
            return $this->scentProfiles->map(function ($item) {
                return [
                    'scent_group_id' => $item->scent_group_id,
                    'strength' => $item->strength,
                ];
            });
        }),

        'variants' => $this->whenLoaded('variants', function () {
            return $this->variants->map(function ($variant) {
                return [
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock' => $variant->stock,
                    'sold' => $variant->sold,
                    'status' => $variant->status,
                    'barcode' => $variant->barcode,
                    'description' => $variant->description,
                ];
            });
        }),

        'images' => $this->whenLoaded('images', function () {
            return $this->images->map(fn($img) => $img->image_url);
        }),
    ];
}

}
