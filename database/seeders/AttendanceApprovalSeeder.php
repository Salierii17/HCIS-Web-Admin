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
        // First, create an attendance record that is 'Incomplete'
        $incompleteAttendance = Attendance::factory()->create([
            'date' => Carbon::yesterday()->subDays(10)->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => null,
            'approval_status' => 'Incomplete', // Manually set the status for the test case
            'employee_id' => 1, // Assuming user with ID 1 exists
        ]);

        // Now, create a pending approval request linked to that attendance record
        AttendanceApproval::create([
            'attendance_id' => $incompleteAttendance->id,
            'requested_by_id' => 1, // The employee requested it
            'requested_clock_out_time' => '17:30:00',
            'employee_reason' => 'This is a seeded request. I forgot to clock out.',
            'status' => 'pending',
        ]);
    }
}
