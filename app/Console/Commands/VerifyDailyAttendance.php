<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyDailyAttendance extends Command
{
    protected $signature = 'attendance:daily-verify';

    protected $description = 'Verifies all of yesterday\'s and today\'s attendance records, flagging incomplete or non-compliant ones.';

    public function handle(): int
    {
        Log::info('===== Running Daily Attendance Verification =====');
        $this->info('Starting daily attendance processing for YESTERDAY and TODAY...');

        // Process yesterday's records
        $this->processAttendanceForDate(Carbon::yesterday());

        // Process today's records
        $this->processAttendanceForDate(Carbon::today());

        $this->info('Process finished successfully.');
        Log::info('===== Command Finished =====');

        return self::SUCCESS;
    }

    /**
     * Processes all "In Progress" attendance records for a given date.
     *
     * @return void
     */
    private function processAttendanceForDate(Carbon $dateToProcess)
    {
        $dateString = $dateToProcess->toDateString();
        Log::info("--- Processing records for date: {$dateString} ---");

        // Handle Incomplete Records (records with no clock-out)
        $incompleteQuery = Attendance::where('date', $dateString)
            ->where('approval_status', 'In Progress')
            ->whereNull('clock_out_time');

        // Use clone() so the count() doesn't affect the update() query builder state
        $incompleteCount = $incompleteQuery->clone()->count();
        if ($incompleteCount > 0) {
            $incompleteQuery->update(['approval_status' => 'Incomplete']);
            $this->info("Flagged {$incompleteCount} incomplete attendance records for {$dateString}.");
            Log::info("Flagged {$incompleteCount} incomplete records for {$dateString}.");
        }

        // Handle and Verify Complete Records
        $recordsToVerify = Attendance::where('date', $dateString)
            ->where('approval_status', 'In Progress')
            ->whereNotNull('clock_out_time')
            ->get();

        if ($recordsToVerify->isEmpty()) {
            $this->info("No completed records on {$dateString} needed verification.");

            return; // Exit this function for this date
        }

        $verifiedCount = 0;
        $flaggedCount = 0;

        foreach ($recordsToVerify as $record) {
            Log::info("Processing record ID: {$record->id} for date {$dateString}");
            // Calculate and save work hours
            $clockIn = Carbon::parse($record->clock_in_time);
            $clockOut = Carbon::parse($record->clock_out_time);
            $workHours = round($clockOut->diffInSeconds($clockIn) / 3600, 2);
            $record->work_hours = $workHours;

            // Run validation rules
            $hoursAreValid = ($workHours >= 8);
            // $locationIsValid = $this->isLocationValid($record);

            // Update status based on rules
            if ($hoursAreValid) {
                $record->approval_status = 'Verified';
                $verifiedCount++;
            } else {
                $record->approval_status = 'Flagged for Review';
                $flaggedCount++;
            }
            $record->save();
        }

        $this->info("For {$dateString}: Automatically verified {$verifiedCount} records and flagged {$flaggedCount} for review.");
        Log::info("For {$dateString}: Verified {$verifiedCount}, Flagged {$flaggedCount}.");
    }
}
