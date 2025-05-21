<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(), // hoặc bạn có thể chỉ định ID nếu đã có sẵn product
            'image_url' => $this->faker->imageUrl(640, 480, 'product', true), // URL ảnh giả lập
        ];
    }
}
