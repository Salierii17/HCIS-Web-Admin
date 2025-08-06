<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\AttendanceApproval;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Test Case 1: Request to change CLOCK-OUT time only ---
        $attendance1 = Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(10),
            'clock_in_time' => '09:05:00',
            'clock_out_time' => null,
            'approval_status' => 'Incomplete',
            'employee_id' => 1,
        ]);

        AttendanceApproval::create([
            'attendance_id' => $attendance1->id,
            'requested_by_id' => 1,
            'requested_clock_out_time' => '17:30:00',
            'employee_reason' => 'Forgot to clock out.',
            'status' => 'pending',
        ]);

        // --- Test Case 2: Request to change CLOCK-IN time only ---
        $attendance2 = Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(11),
            'clock_in_time' => '09:30:00', // Incorrect clock-in
            'clock_out_time' => '17:30:00',
            'approval_status' => 'Verified',
            'employee_id' => 1,
        ]);

        AttendanceApproval::create([
            'attendance_id' => $attendance2->id,
            'requested_by_id' => 1,
            'requested_clock_in_time' => '09:00:00', // Corrected clock-in
            'employee_reason' => 'Clocked in late by mistake.',
            'status' => 'pending',
        ]);

        // --- Test Case 3: Request to change WORK ARRANGEMENT only ---
        $attendance3 = Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(12),
            'clock_in_time' => '08:55:00',
            'clock_out_time' => '17:05:00',
            'location_type_id' => 1, // Currently WFO
            'approval_status' => 'Verified',
            'employee_id' => 1,
        ]);

        AttendanceApproval::create([
            'attendance_id' => $attendance3->id,
            'requested_by_id' => 1,
            'requested_location_type_id' => 2, // Requesting change to WFA (ID 2)
            'employee_reason' => 'Was approved to work from anywhere this day.',
            'status' => 'pending',
        ]);

        // --- Test Case 4: Request to change ALL THREE fields ---
        $attendance4 = Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(13),
            'clock_in_time' => '10:00:00', // Incorrect
            'clock_out_time' => null,      // Incorrect
            'location_type_id' => 1,       // Incorrect
            'approval_status' => 'Incomplete',
            'employee_id' => 1,
        ]);

        AttendanceApproval::create([
            'attendance_id' => $attendance4->id,
            'requested_by_id' => 1,
            'requested_clock_in_time' => '08:00:00',
            'requested_clock_out_time' => '17:00:00',
            'requested_location_type_id' => 2,
            'employee_reason' => 'Complete record correction needed for this day.',
            'status' => 'pending',
        ]);
    }
}
