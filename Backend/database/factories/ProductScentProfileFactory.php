<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\ScentGroup;

class ProductScentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::inRandomOrder()->value('id') ?? Product::factory(),
            'scent_group_id' => ScentGroup::inRandomOrder()->value('id') ?? ScentGroup::factory(),
            'strength' => $this->faker->numberBetween(0, 100),
        ];
    }
}
