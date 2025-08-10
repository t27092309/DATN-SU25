<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductVariant;
use App\Http\Resources\ProductVariantResource; // Import ProductVariantResource

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $productId = null;
        $productName = 'Sản phẩm không xác định';
        $productSlug = '';
        $productImage = null;
        $displayPrice = (float) $this->price;
        $variantData = null;
        $availableVariants = [];

        // Lấy thông tin sản phẩm và biến thể nếu có
        if ($this->whenLoaded('variant')) {
            $variant = $this->variant;

            if ($variant->relationLoaded('product')) {
                $product = $variant->product;
                if ($product) {
                    $productId = $product->id;
                    $productName = $product->name;
                    $productSlug = $product->slug;
                    $productImage = $product->image;
                }

                if ($product->relationLoaded('variants')) {
                    $availableVariants = ProductVariantResource::collection($product->variants);
                }
            }

            $variantData = new ProductVariantResource($variant);

        } elseif ($this->whenLoaded('product')) {
            $product = $this->product;
            $productId = $product->id;
            $productName = $product->name;
            $productSlug = $product->slug;
            $productImage = $product->image;
        }

        $fullThumbnailUrl = $productImage ? (filter_var($productImage, FILTER_VALIDATE_URL) ? $productImage : Storage::url($productImage)) : null;

        return [
            'id' => $this->id,
            'product_id' => $productId,
            'product_name' => $productName,
            'slug' => $productSlug,
            'price' => round($displayPrice, 2),
            'quantity' => $this->quantity,
            'thumbnail_url' => $fullThumbnailUrl,
            'variant' => $variantData,
            'available_variants' => $availableVariants,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
