<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $statuses = [
            ['status' => 'Present', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Absent', 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Half Day', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('attendance_statuses')->insert($statuses);
    }
}
