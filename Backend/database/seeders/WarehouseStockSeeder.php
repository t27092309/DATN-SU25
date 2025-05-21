<?php

namespace Database\Seeders;

use App\Models\WarehouseStock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WarehouseStock::factory()->count(10)->create();
    }
}
