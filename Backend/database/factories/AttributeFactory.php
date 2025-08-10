<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        // Đảm bảo slug luôn unique bằng cách nối thêm số random
        $name = $this->faker->unique()->word();
        $slug = Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 100000));

        return [
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
