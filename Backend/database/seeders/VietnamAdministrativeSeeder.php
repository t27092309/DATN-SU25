<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VietnamAdministrativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        DB::unprepared(file_get_contents(database_path('sql/CreateTables_vn_units.sql')));
        DB::unprepared(file_get_contents(database_path('sql/ImportData_vn_units.sql')));
    }
}
