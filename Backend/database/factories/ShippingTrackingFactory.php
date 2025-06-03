<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;

class ShippingTrackingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'shipped', 'in_transit', 'delivered', 'failed']);

        $shippedAt = in_array($status, ['shipped', 'in_transit', 'delivered']) 
            ? $this->faker->dateTimeBetween('-1 week', 'now') 
            : null;

        $deliveredAt = $status === 'delivered' 
            ? $this->faker->dateTimeBetween($shippedAt ?? '-1 week', 'now') 
            : null;

        return [
            'order_id' => Order::inRandomOrder()->value('id') ?? Order::factory(),
            'tracking_number' => $this->faker->uuid(),
            'status' => $status,
            'shipped_at' => $shippedAt,
            'delivered_at' => $deliveredAt,
        ];
    }
}
