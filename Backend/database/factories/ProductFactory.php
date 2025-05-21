<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true); // tạo tên sản phẩm dài hơn
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'image' => fake()->imageUrl(600, 600, 'products'),
            'price' => fake()->randomFloat(2, 100, 1000),
            'description' => fake()->paragraph(3),
            'gender' => fake()->randomElement(['male', 'female', 'unisex']),
            'category_id' => 1, // cập nhật sau bằng ID thực tế hoặc random
            'brand_id' => 1,    // cập nhật sau bằng ID thực tế hoặc random
        ];
    }
}
