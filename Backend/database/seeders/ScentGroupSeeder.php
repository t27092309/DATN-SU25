<?php

namespace Database\Seeders;

use App\Models\ScentGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScentGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ScentGroup::factory()->count(100)->create();
    }
}
