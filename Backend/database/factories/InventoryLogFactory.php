<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariant;
use App\Models\Warehouse;

class InventoryLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['import', 'export', 'adjustment']);

        // Xác định quantity_change dựa vào type
        $quantityChange = match ($type) {
            'import' => $this->faker->numberBetween(1, 100),
            'export' => -$this->faker->numberBetween(1, 100),
            'adjustment' => $this->faker->numberBetween(-50, 50),
        };

        return [
            'product_variant_id' => ProductVariant::inRandomOrder()->value('id') ?? ProductVariant::factory(),
            'warehouse_id' => Warehouse::inRandomOrder()->value('id') ?? Warehouse::factory(),
            'quantity_change' => $quantityChange,
            'type' => $type,
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
