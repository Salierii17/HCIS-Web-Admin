<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        Log::info('--- Running attendance:daily-verify command ---');
        Log::info('Timezone from config/app.php: ' . config('app.timezone'));
        Log::info('Carbon::now() reports current time as: ' . Carbon::now()->toDateTimeString());

        $yesterday = Carbon::yesterday()->toDateString();
        Log::info('Based on the time above, the command is searching for date: ' . $yesterday);

        $this->info('Starting daily attendance processing...');

        // Handle Incomplete Records (records with no clock-out)
        $incompleteQuery = Attendance::where('date', $yesterday)
            ->where('approval_status', 'In Progress')
            ->whereNull('clock_out_time');

        $incompleteCount = $incompleteQuery->count();
        Log::info("Query for incomplete records found {$incompleteCount} item(s).");

        if ($incompleteCount > 0) {
            $incompleteQuery->update(['approval_status' => 'Incomplete']);
            $this->info("Flagged {$incompleteCount} incomplete attendance records.");
        }

        // Handle and Verify Complete Records
        $recordsToVerify = Attendance::where('date', $yesterday)
            ->where('approval_status', 'In Progress')
            ->whereNotNull('clock_out_time')
            ->get();

        Log::info("Query for completed records found {$recordsToVerify->count()} item(s) to verify.");


        if ($recordsToVerify->isEmpty()) {
            $this->info('No completed records needed verification. Process finished.');
            Log::info('--- Command finished: No completed records found. ---');
            return self::SUCCESS;
        }

        $verifiedCount = 0;
        $flaggedCount = 0;

        foreach ($recordsToVerify as $record) {
            Log::info("Processing record ID: {$record->id}");
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
        Log::info('--- Command finished: Processed all records. ---');


        return self::SUCCESS;
    }
}
