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
        // Test Case 1: Standard Full Day (> 8 hours)
        Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(1)->toDateString(),
            'clock_in_time' => '08:30:00',
            'clock_out_time' => '17:30:00', // 9 hours elapsed -> Observer should calculate 8 work hours
            'gps_coordinates' => '-6.2383, 106.9924',
        ]);

        // Test Case 2: Specific Half-Day Policy (8 AM to < 1 PM)
        Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(2)->toDateString(),
            'clock_in_time' => '08:00:00',
            'clock_out_time' => '12:30:00', // 4.5 hours elapsed -> 4.5 work hours.
        ]);

        // Test Case 3: General Half-Day Rule (5-8 hours)
        Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(3)->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '16:00:00', // 7 hours elapsed -> Observer should calculate 6 work hours.
        ]);

        // Test Case 4: Absent Rule (< 5 hours)
        Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(4)->toDateString(),
            'clock_in_time' => '10:00:00',
            'clock_out_time' => '14:00:00', // 4 hours elapsed -> 4 work hours.
        ]);

        // Test Case 5: Incomplete Record (for the daily command to find)
        Attendance::factory()->create([
            'date' => Carbon::yesterday()->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
        ]);

        // Test Case 6: Shift that does NOT span the break time.
        Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(5)->toDateString(),
            'clock_in_time' => '13:00:00',
            'clock_out_time' => '18:00:00', // 5 hours elapsed -> 5 work hours.
        ]);
    }
}
