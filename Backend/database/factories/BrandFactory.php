<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;


class BrandFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        return [
            'name' => fake()->company(),
            'slug' => fake()->company(),
        ];
    }
}
