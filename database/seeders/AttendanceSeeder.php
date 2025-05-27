<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Attendance::insert([
            [
                'employee_id' => 1,
                'date' => Carbon::now()->subDays(1)->toDateString(),
                'clock_in_time' => '08:05:00',
                'clock_out_time' => '17:01:00',
                'location_type_id' => 1,
                'gps_coordinates' => '-6.2431,106.8412',
                'status_id' => 1,
                'work_hours' => 8.5,
                'notes' => 'Normal workday',
                 'created_at' => now(), // Not strictly needed, Eloquent handles it
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => Carbon::now()->subDays(2)->toDateString(),
                'clock_in_time' => '09:15:00',
                'clock_out_time' => '18:00:00',
                'location_type_id' => 2,
                'gps_coordinates' => '-6.2011,106.8070',
                'status_id' => 2,
                'work_hours' => 7.75,
                'notes' => 'Work from home',
                'created_at' => now(), // Not strictly needed, Eloquent handles it
                'updated_at' => now()
            ],
            [
                'employee_id' => 1,
                'date' => Carbon::now()->subDays(3)->toDateString(),
                'clock_in_time' => '08:10:00',
                'clock_out_time' => '17:00:00',
                'location_type_id' => 1,
                'gps_coordinates' => '-6.2445,106.8410',
                'status_id' => 1,
                'work_hours' => 8.2,
                'notes' => 'Office work',
                'created_at' => now(), // Not strictly needed, Eloquent handles it
                'updated_at' => now()
            ],
        ]);
    }
}
