<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage; // Đảm bảo import Storage

class ProductImageResource extends JsonResource
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
            // Trả về URL đầy đủ của ảnh
            'image_url' => Storage::url($this->image_url),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}