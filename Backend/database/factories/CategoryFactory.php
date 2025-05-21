<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
       $name = $this->faker->unique()->word();
        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
