<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;

class CartItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cart_id' => Cart::inRandomOrder()->value('id') ?? Cart::factory(),
            'product_id' => Product::inRandomOrder()->value('id') ?? Product::factory(),
            'product_variant_id' => ProductVariant::inRandomOrder()->value('id') ?? null,
            'quantity' => $this->faker->numberBetween(1, 10),
            'price' => $this->faker->randomFloat(2, 100, 1000),
        ];
    }
}
