<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image,
            'gender' => $this->gender,
            'price' => $this->price,
            'view' => $this->views,
            'description' => $this->description,

            // Include brand_name when the 'brand' relationship is loaded
            'brand_id' => $this->brand_id, // Still good to include the ID
            'brand_name' => $this->whenLoaded('brand', function () {
                return $this->brand->name;
            },null),
            'brand_slug' => $this->whenLoaded('brand', function () {
                return $this->brand->slug;
            }),

            // Include category_name when the 'category' relationship is loaded
            'category_id' => $this->category_id, // Still good to include the ID
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
            'category_slug' => $this->whenLoaded('category', function () {
                return $this->category->slug;
            }),

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
                        'scent_group_name' => $item->scentGroup->name,
                        'scent_group_color_code' => $item->scentGroup->color_code,
                        'strength' => $item->strength,
                    ];
                });
            }),

            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'stock' => $variant->stock,
                        'sold' => $variant->sold,
                        'status' => $variant->status,
                        'barcode' => $variant->barcode,
                        'description' => $variant->description,
                        'attributes' => $variant->relationLoaded('attributeValues') ?
                            $variant->attributeValues->filter(function ($attributeValue) {
                                return $attributeValue->relationLoaded('attribute') && $attributeValue->attribute !== null;
                            })->map(function ($attributeValue) {
                                return [
                                    'attribute_name' => $attributeValue->attribute->name,
                                    'attribute_slug' => $attributeValue->attribute->slug,
                                    'value_id' => $attributeValue->id,
                                    'value_name' => $attributeValue->value,
                                ];
                            }) : [],
                    ];
                });
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => $img->image_url);
            }),
        ];
    }
}
