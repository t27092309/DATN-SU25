<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
  public function toArray(Request $request): array
    {
        $data = [
            'name' => $this->name, // Tên sản phẩm
            'category_name' => $this->whenLoaded('category', fn () => $this->category->name ?? 'No Category'), // Danh mục
            'image' => $this->image ? Storage::url($this->image) : ($this->images->first()?->path ? Storage::url($this->images->first()->path) : 'https://via.placeholder.com/600x600.png'), // Ảnh
            'variants' => $this->whenLoaded('variants', function () { // Biến thể
                return $this->variants->map(function ($variant) {
                    return [
                        'name' => $variant->sku ?? 'No SKU',
                        'price' => $variant->price ?? 0,
                    ];
                });
            }, []),
            'price' => $this->price ?? $this->variants->min('price') ?? 0, // Giá
            'brand' => $this->whenLoaded('brand', fn () => $this->brand->name ?? 'No Brand'), // Hãng
            'gender' => $this->gender ?? 'Unknown', // Giới tính
        ];

        // Thêm trường cho trang chủ nếu query param 'for' = 'home'
        if ($request->query('for') === 'home') {
            $data['id'] = $this->id;
            $data['slug'] = $this->slug;
            $data['views'] = $this->views ?? 0;
        }

        return $data;
    }
}
