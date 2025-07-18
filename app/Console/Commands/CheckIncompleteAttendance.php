<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckIncompleteAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:check-incomplete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds attendance records from yesterday with no clock-out and flags them as incomplete.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $incompleteCount = Attendance::where('date', $yesterday)
            ->whereNotNull('clock_in_time')
            ->whereNull('clock_out_time')
            ->update(['approval_status' => 'Incomplete']); // Use 'Incomplete' from your enum

        $this->info("Found and updated {$incompleteCount} incomplete attendance records for {$yesterday}.");

        return 0;
    }
}
