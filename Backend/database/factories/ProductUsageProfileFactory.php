<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductUsageProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Tạo phần trăm mùa sao cho tổng không vượt quá 100
        $spring = $this->faker->numberBetween(0, 100);
        $remaining = 100 - $spring;
        $summer = $this->faker->numberBetween(0, $remaining);
        $remaining -= $summer;
        $autumn = $this->faker->numberBetween(0, $remaining);
        $winter = 100 - ($spring + $summer + $autumn);

        return [
            'product_id' => Product::factory(), // Hoặc chỉ định ID nếu đã có
            'spring_percent' => $spring,
            'summer_percent' => $summer,
            'autumn_percent' => $autumn,
            'winter_percent' => $winter,
            'suitable_day' => $this->faker->numberBetween(0, 100),
            'suitable_night' => $this->faker->numberBetween(0, 100),
            'longevity_hours' => round($this->faker->randomFloat(1, 1, 24), 1),
            'sillage_range_m' => $this->faker->randomElement(['0.5m', '1m', '2m', '3m+', 'room-filling']),
        ];
    }
}
