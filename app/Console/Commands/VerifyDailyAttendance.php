<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class VerifyDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:daily-verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifies all of yesterday\'s attendance records, flagging incomplete or non-compliant ones.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting daily attendance processing...');
        $yesterday = Carbon::yesterday()->toDateString();

        // Handle Incomplete Records (records with no clock-out) 
        $incompleteCount = Attendance::where('date', $yesterday)
            ->where('approval_status', 'In Progress')
            ->whereNull('clock_out_time')
            ->update(['approval_status' => 'Incomplete']);

        if ($incompleteCount > 0) {
            $this->info("Flagged {$incompleteCount} incomplete attendance records.");
        }

        // Handle and Verify Complete Records
        $recordsToVerify = Attendance::where('date', $yesterday)
            ->where('approval_status', 'In Progress')
            ->whereNotNull('clock_out_time')
            ->get();

        if ($recordsToVerify->isEmpty()) {
            $this->info('No completed records needed verification. Process finished.');
            return self::SUCCESS;
        }

        $verifiedCount = 0;
        $flaggedCount = 0;

        foreach ($recordsToVerify as $record) {
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
        $this->info("Automatically verified {$verifiedCount} compliant records.");
        $this->info("Flagged {$flaggedCount} non-compliant records for supervisor review.");
        $this->info('Process finished successfully.');

        return self::SUCCESS;
    }
}
