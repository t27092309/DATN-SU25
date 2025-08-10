<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserFactory::createDefaultUsers('adminflorea', 'staffflorea', 'user_pass');
        $this->command->info('Default users (Admin, Staff, Users) have been processed with custom passwords.');
    }
}
