<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Warehouse;
use App\Models\ProductVariant;

class WarehouseStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::inRandomOrder()->value('id') ?? Warehouse::factory(),
            'product_variant_id' => ProductVariant::inRandomOrder()->value('id') ?? ProductVariant::factory(),
            'stock_quantity' => $this->faker->numberBetween(10, 500),
        ];
    }
}
