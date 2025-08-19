<?php

namespace App\Console\Commands;

use App\Models\JobOpenings;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ManageJobOpeningStatus extends Command
{
    protected $signature = 'job-openings:manage-status';
    protected $description = 'Automatically manage job opening statuses based on dates';

    public function handle()
    {
        $now = Carbon::now();
        
        // Open jobs that have reached their opening date
        $jobsToOpen = JobOpenings::where('DateOpened', '<=', $now)
            ->where('Status', 'New')
            ->where('published_career_site', 0)
            ->get();

        foreach ($jobsToOpen as $job) {
            $job->update([
                'Status' => 'Opened',
                'published_career_site' => 1
            ]);
        }

        // Close expired jobs
        $expiredJobs = JobOpenings::where('TargetDate', '<=', $now)
            ->where('Status', '!=', 'Closed')
            ->get();

        foreach ($expiredJobs as $job) {
            $job->update([
                'Status' => 'Closed',
                'published_career_site' => 0
            ]);
        }

        $this->info('Job openings statuses updated successfully.');
        return 0;
    }
}
