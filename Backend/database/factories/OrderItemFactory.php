<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Size;

class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $productVariant = ProductVariant::inRandomOrder()->first() ?? ProductVariant::factory()->create();


        return [
            'order_id' => Order::inRandomOrder()->value('id') ?? Order::factory(),
            'product_variant_id' => $productVariant->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'price_each' => $productVariant->price ?? $this->faker->randomFloat(2, 50, 1000),
            // Thông tin phụ để tránh join trong hệ thống thực tế
            'variant_sku' => $productVariant->sku,
            'variant_description' => $productVariant->description ?? $this->faker->sentence(),
            'variant_name' => 'Variant #' . $productVariant->id,
        ];
    }
}
