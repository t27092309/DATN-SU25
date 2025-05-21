<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('now','+4 days');

        return [
            'name' => $this->faker->company . ' Shipping',
            'price' => $this->faker->randomFloat(2, 5, 100), // giá từ 5 đến 100
            'expected_delivery_date' => $date ? $date ->format('Y-m-d') : null,
        ];
    }
}
    