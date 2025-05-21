<?php

namespace Database\Seeders;

use App\Models\ShippingTracking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShippingTrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingTracking::factory()->count(10)->create();
    }
}
