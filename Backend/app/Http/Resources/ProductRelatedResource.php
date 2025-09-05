<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductRelatedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'image' => $this->image
                ? config('app.url') . '/' . ltrim(Storage::url($this->image), '/')
                : 'https://via.placeholder.com/600x600.png',
            'price' => $this->variants->min('price') ?? $this->price ?? 0,
            'brand' => $this->whenLoaded('brand', fn() => $this->brand->name ?? 'No Brand'),
            'category_name' => $this->whenLoaded('category', fn() => $this->category->name ?? 'No Category'),
        ];
    }
}
