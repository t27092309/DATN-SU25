<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Size;

class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusOptions = ['available', 'out_of_stock', 'discontinued'];

        return [
            'product_id' => Product::factory(), // Hoặc bạn có thể gán ID nếu có sẵn sản phẩm
            'price' => $this->faker->randomFloat(2, 50000, 500000), // 50k - 500k
            'stock' => $this->faker->numberBetween(0, 100),
            'sold' => $this->faker->numberBetween(0, 100),
            'sku' => strtoupper($this->faker->unique()->bothify('SKU-###??')),
            'barcode' => $this->faker->optional()->ean13(),
            'description' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement($statusOptions),
        ];
    }
}
