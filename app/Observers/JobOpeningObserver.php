<?php

namespace App\Observers;

use App\Models\JobOpenings;
use Carbon\Carbon;

class JobOpeningObserver
{
    public function saving(JobOpenings $jobOpening)
    {
        $now = Carbon::now();
        
        // If DateOpened is in the past, automatically open and publish
        if ($jobOpening->DateOpened <= $now && $jobOpening->Status === 'New') {
            $jobOpening->Status = 'Opened';
            $jobOpening->published_career_site = true;
        }
        
        // If TargetDate is in the past, automatically close and unpublish
        if ($jobOpening->TargetDate <= $now && $jobOpening->Status !== 'Closed') {
            $jobOpening->Status = 'Closed';
            $jobOpening->published_career_site = false;
        }
    }
}