<?php

namespace Database\Factories;

use App\Models\Category; // Make sure these are imported
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true); // Get a unique name to start with

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'image' => $this->faker->imageUrl(600, 600, 'products', true, 'provident'),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'description' => $this->faker->paragraph(),
            'gender' => $this->faker->randomElement(['male', 'female', 'unisex']),
            'category_id' => Category::inRandomOrder()->first()->id,
            'brand_id' => Brand::inRandomOrder()->first()->id,
            'views' => $this->faker->numberBetween(100, 100000),
        ];
    }
}