<?php

namespace Database\Seeders;

use App\Models\InventoryLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventoryLog::factory()->count(10)->create();
    }
}
