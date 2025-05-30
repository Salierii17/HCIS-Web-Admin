<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkArrangementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $types = ['WFO', 'WFH'];
        foreach ($types as $type) {
            \App\Models\WorkArrangement::create(['arrangement_type' => $type]);
        }
    }
}
