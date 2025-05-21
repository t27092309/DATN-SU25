<?php

namespace Database\Seeders;

use App\Models\OrderReturn;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderReturnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderReturn::factory()->count(10)->create();
    }
}
