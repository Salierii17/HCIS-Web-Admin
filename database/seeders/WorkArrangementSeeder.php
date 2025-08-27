<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkArrangementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('work_arrangements')->insert([
            ['arrangement_type' => 'WFO'],
            ['arrangement_type' => 'WFA'],
        ]);
    }
}
