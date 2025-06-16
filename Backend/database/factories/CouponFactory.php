<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $discountType = $this->faker->randomElement(['percent', 'fixed']);

        return [
            'code' => strtoupper($this->faker->bothify('COUPON##??')), // Ví dụ: COUPON23AB
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'percent'
                ? $this->faker->numberBetween(5, 50) // phần trăm từ 5% đến 50%
                : $this->faker->randomFloat(2, 10000, 500000), // số tiền cố định từ 10k đến 500k
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
