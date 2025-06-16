<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Cart;
use App\Models\ProductVariant;

class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::inRandomOrder()->value('id') ?? Cart::factory(),
            'product_variant_id' => ProductVariant::inRandomOrder()->value('id') ?? ProductVariant::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
        ];
    }
}
