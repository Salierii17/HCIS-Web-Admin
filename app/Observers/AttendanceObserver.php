<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;


class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "deleted" event.
     */
    public function deleted(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "restored" event.
     */
    public function restored(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "force deleted" event.
     */
    public function forceDeleted(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "saving" event.
     */
    public function saving(Attendance $attendance): void
    {
        // This logic only runs if there is a clock_in and a clock_out time.
        if ($attendance->clock_in_time && $attendance->clock_out_time) {
            $clockIn = Carbon::parse($attendance->clock_in_time);
            $clockOut = Carbon::parse($attendance->clock_out_time);

            // Calculate duration in hours
            $durationInMinutes = $clockOut->diffInMinutes($clockIn);
            $durationInHours = $durationInMinutes / 60.0;

            // Store the calculated decimal hours
            $attendance->work_hours = round($durationInHours, 2);

            // Apply the new attendance status rules
            $this->setStatusBasedOnDuration($attendance, $clockIn, $clockOut, $durationInHours);
        }
    }

    /**
     * Sets the status_id based on the new business rules.
     */
    private function setStatusBasedOnDuration(Attendance $attendance, Carbon $clockIn, Carbon $clockOut, float $durationInHours): void
    {
        // Define your business rule constants
        $standardWorkdayHours = 8.0;
        $halfDayThresholdHours = 5.0;

        // Specific Half-day Policy: 8 AM clock-in, clock-out before 1 PM
        if ($clockIn->format('H:i') === '08:00' && $clockOut->hour < 13) {
            $attendance->status_id = $this->getStatusId('Half Day');

            return; // Exit after applying the specific rule
        }

        // General Duration-Based Rules
        if ($durationInHours < $halfDayThresholdHours) {
            $attendance->status_id = $this->getStatusId('Absent');
        } elseif ($durationInHours >= $halfDayThresholdHours && $durationInHours < $standardWorkdayHours) {
            $attendance->status_id = $this->getStatusId('Half Day');
        } else { // 8 hours or more
            $attendance->status_id = $this->getStatusId('Present');
        }
    }

    /**
     * Helper to get the ID from the attendance_statuses table.
     */
    private function getStatusId(string $statusName): ?int
    {
        static $statuses = [];
        if (empty($statuses)) {
            $statuses = AttendanceStatus::all()->pluck('id', 'status')->toArray();
        }

        return $statuses[$statusName] ?? null;
    }
}
