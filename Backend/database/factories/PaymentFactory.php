<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;

class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'paid', 'failed']);

        return [
            'order_id' => Order::inRandomOrder()->value('id') ?? Order::factory(),
            'payment_method' => $this->faker->randomElement(['cash', 'momo', 'vnpay']),
            'payment_status' => $status,
            'paid_at' => $status === 'paid' ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
