<?php

namespace Database\Seeders;

use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use App\Models\Referral;
use App\Models\Referrals;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReferralSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Referrals with different scenarios...');

        $employees = User::all();
        $candidates = Candidates::all();
        $openJobs = JobOpenings::where('Status', 'Opened')->get();

        if ($candidates->count() < 5 || $employees->count() < 2 || $openJobs->isEmpty()) {
            $this->command->error('Not enough data to seed referral scenarios.');

            return;
        }

        // Scenario 1: Standard Referral for an open job
        $jobCandidate = JobCandidates::where('CandidateSource', '!=', 'Employee Referral')->first();
        if ($jobCandidate) {
            Referrals::firstOrCreate(
                ['JobCandidate' => $jobCandidate->id],
                [
                    'resume' => 'resumes/referred_candidate_1.pdf',
                    'ReferringJob' => $jobCandidate->JobId,
                    'Candidate' => $jobCandidate->candidate,
                    'ReferredBy' => $employees->random()->id,
                    'AssignedRecruiter' => $employees->random()->id,
                    'Relationship' => 'Former Colleague',
                ]
            );
            // Update the source
            $jobCandidate->update(['CandidateSource' => 'Employee Referral']);
            $this->command->info('Scenario 1: Created a standard referral.');
        }

        // Scenario 2: Executive Referral for a high-priority role
        $ibJob = JobOpenings::where('JobTitle', 'Investment Banking Analyst')->first();
        $ibCandidate = $candidates->get(10);
        $executive = User::first(); // Assuming the first user is an executive
        if ($ibJob && $ibCandidate && $executive) {
            $jobApp = JobCandidates::create([
                'JobId' => $ibJob->id,
                'candidate' => $ibCandidate->id,
                'Email' => $ibCandidate->email,
                'CandidateStatus' => 'Screening',
                'CandidateSource' => 'Employee Referral',
            ]);
            Referrals::firstOrCreate(
                ['Candidate' => $ibCandidate->id, 'ReferringJob' => $ibJob->id],
                [
                    'resume' => 'resumes/exec_referred.pdf',
                    'JobCandidate' => $jobApp->id,
                    'ReferredBy' => $executive->id,
                    'AssignedRecruiter' => $employees->get(1)->id,
                    'Relationship' => 'Personal Network',
                    'Notes' => 'High-priority candidate, please expedite.',
                ]
            );
            $this->command->info('Scenario 2: Created an executive referral.');
        }

        // Scenario 3: Referral for a job that is not yet public
        $futureJob = JobOpenings::where('Status', 'New')->first();
        $prospectCandidate = $candidates->get(12);
        if ($futureJob && $prospectCandidate) {
            Referrals::firstOrCreate(
                ['Candidate' => $prospectCandidate->id, 'ReferringJob' => $futureJob->id],
                [
                    'resume' => 'resumes/prospect.pdf',
                    'JobCandidate' => null, // No application yet
                    'ReferredBy' => $employees->random()->id,
                    'AssignedRecruiter' => $employees->random()->id,
                    'Relationship' => 'Industry Peer',
                    'Notes' => 'Good fit for the upcoming role.',
                ]
            );
            $this->command->info('Scenario 3: Created a referral for a future job opening.');
        }
    }
}
