<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'views' => $this->views,
            'image' => $this->image,
            'brand' => $this->whenLoaded('brand', fn () => $this->brand->name),
            // thêm trường tùy theo nhu cầu
        ];
    }
}
