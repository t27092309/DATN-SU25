<?php

namespace Database\Seeders;

use App\Models\ProductScentProfile;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductScentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductScentProfile::factory()->count(10)->create();
    }
}
