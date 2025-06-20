<?php

namespace Database\Seeders;

use App\Models\ProductUsageProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductUsageProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductUsageProfile::factory()->count(1000)->create();
    }
}
