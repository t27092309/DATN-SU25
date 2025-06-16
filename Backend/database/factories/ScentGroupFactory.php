<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ScentGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),                 // tên nhóm mùi
            'color_code' => fake()->hexColor(),                // mã màu hex (ví dụ: #ffcc00)
        ];
    }
}
