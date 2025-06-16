<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Warehouse',
            'location' => fake()->address(),
        ];
    }
}
