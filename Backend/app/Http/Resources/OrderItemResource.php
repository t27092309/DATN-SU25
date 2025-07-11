<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
     public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'product_variant' => new ProductVariantResource($this->whenLoaded('productVariant')),
            'variant_name'    => $this->variant_name,
            'variant_sku'     => $this->variant_sku,
            'quantity'        => $this->quantity,
            'price_each'      => $this->price_each,
        ];
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
}
