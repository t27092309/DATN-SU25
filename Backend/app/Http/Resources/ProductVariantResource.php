<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'sku' => $this->sku,
            'price' => (float) $this->price, // Đảm bảo là float
            'stock' => (int) $this->stock,   // Đảm bảo là integer
            'status' => $this->status,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at, // Nếu dùng soft deletes

            // Bao gồm thông tin sản phẩm cha
            'product' => new ProductResource($this->whenLoaded('product')), // Đảm bảo ProductResource đã có

            // Bao gồm các giá trị thuộc tính liên quan đến biến thể này (nhiều-nhiều)
            'attribute_values' => AttributeValueResource::collection($this->whenLoaded('attributeValues')), // Đảm bảo AttributeValueResource đã có
        ];
    }
}