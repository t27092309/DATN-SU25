<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;

class OrderReturnFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['requested', 'approved', 'rejected', 'processed'];

        return [
            'order_id' => Order::inRandomOrder()->value('id') ?? Order::factory(),
            'reason' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement($statuses),
        ];
    }
}
