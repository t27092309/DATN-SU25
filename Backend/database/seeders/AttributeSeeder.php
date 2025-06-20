<?php

namespace Database\Seeders;

use App\Models\Attribute; // Don't forget to import the Attribute model
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 fake attributes
        Attribute::factory()->count(50)->create();
    }
}