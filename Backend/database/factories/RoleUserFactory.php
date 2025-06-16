<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleUserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role_id' => \App\Models\Role::inRandomOrder()->first()?->id ?? 1,
            'user_id' => \App\Models\User::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
