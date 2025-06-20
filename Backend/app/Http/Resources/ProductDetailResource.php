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
            'view' => $this->views,
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
                        'scent_group_name' => $item->scentGroup->name,
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
                        // THE FIX IS HERE:
                        // You need to create a new, nested resource for the variant,
                        // or check the relation status directly using wasLoaded().
                        // The simplest and cleanest way for complex nested relations
                        // is often to create a dedicated resource for ProductVariant.
                        // However, if you want to keep it flat, you can do this:
                        'attributes' => $variant->relationLoaded('attributeValues') ? // Use relationLoaded() on the model
                            $variant->attributeValues->filter(function($attributeValue) {
                                // Filter out attributeValues where 'attribute' relation is null
                                return $attributeValue->relationLoaded('attribute') && $attributeValue->attribute !== null;
                            })->map(function ($attributeValue) {
                                return [
                                    'attribute_name' => $attributeValue->attribute->name,
                                    'attribute_slug' => $attributeValue->attribute->slug,
                                    'value_id' => $attributeValue->id,
                                    'value_name' => $attributeValue->value,
                                ];
                            }) : [], // If not loaded, return an empty array
                    ];
                });
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn($img) => $img->image_url);
            }),
        ];
    }
}