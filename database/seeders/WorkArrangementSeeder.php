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
        $statuses = ['Present', 'Late'];
        foreach ($statuses as $status) {
            \App\Models\AttendanceStatus::create(['name' => $status]);
        }
    }
}
