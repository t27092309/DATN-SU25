<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            "user_id" => 1,
            "recipient_name" => fake()->name(),
            "phone_number" => fake()->phoneNumber(),
            "address_line" => fake()->address(),
            "ward" => fake()->citySuffix(),
            "district" => fake()->city(),
            "province" => fake()->state(),
            "is_default" => fake()->boolean(),
        ];
    }
}
