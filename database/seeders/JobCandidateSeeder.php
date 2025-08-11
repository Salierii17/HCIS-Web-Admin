<?php

namespace Database\Seeders;

use App\Models\Candidates;
use App\Models\JobCandidates;
use App\Models\JobOpenings;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JobCandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Job Applications with realistic scenarios...');

        $candidates = Candidates::all();
        $jobOpenings = JobOpenings::where('Status', 'Opened')->get();
        $sources = ['Online Portal', 'LinkedIn', 'Employee Referral', 'Campus Recruitment'];
        $stages = ['New', 'Screening', 'Interviewing', 'Offered', 'Hired'];

        if ($candidates->isEmpty() || $jobOpenings->isEmpty()) {
            $this->command->error('Cannot create job applications. No candidates or open jobs found.');

            return;
        }

        // Scenario 1: High-volume for popular roles (e.g., Analyst)
        $popularJob = JobOpenings::where('JobTitle', 'like', '%Analyst%')->first();
        if ($popularJob) {
            foreach ($candidates->take(25) as $candidate) { // 25 candidates apply
                JobCandidates::firstOrCreate(
                    ['JobId' => $popularJob->id, 'candidate' => $candidate->id],
                    [
                        'Email' => $candidate->email,
                        'ExperienceInYears' => $candidate->ExperienceInYears,
                        'CandidateStatus' => (rand(1, 10) > 3) ? 'New' : 'Screening', // Most are new
                        'CandidateSource' => $sources[array_rand($sources)],
                        'created_at' => Carbon::now()->subDays(rand(1, 45)),
                    ]
                );
            }
            $this->command->info("Scenario: Created 25 applications for popular role: {$popularJob->JobTitle}.");
        }

        // Scenario 2: Fewer, more qualified applicants for niche roles
        $nicheJob = JobOpenings::where('JobTitle', 'like', '%Engineer%')->first();
        if ($nicheJob) {
            foreach ($candidates->skip(25)->take(5) as $candidate) { // Only 5 apply
                JobCandidates::firstOrCreate(
                    ['JobId' => $nicheJob->id, 'candidate' => $candidate->id],
                    [
                        'Email' => $candidate->email,
                        'ExperienceInYears' => $candidate->ExperienceInYears,
                        'CandidateStatus' => $stages[rand(2, 4)], // More advanced stages
                        'CandidateSource' => $sources[array_rand($sources)],
                        'created_at' => Carbon::now()->subDays(rand(5, 60)),
                    ]
                );
            }
            $this->command->info("Scenario: Created 5 qualified applications for niche role: {$nicheJob->JobTitle}.");
        }

        // Scenario 3: General applications for other open roles
        foreach ($candidates->skip(30) as $candidate) {
            $job = $jobOpenings->random();
            JobCandidates::firstOrCreate(
                ['JobId' => $job->id, 'candidate' => $candidate->id],
                [
                    'Email' => $candidate->email,
                    'ExperienceInYears' => $candidate->ExperienceInYears,
                    'CandidateStatus' => $stages[array_rand($stages)],
                    'CandidateSource' => $sources[array_rand($sources)],
                    'created_at' => Carbon::now()->subDays(rand(1, 90)),
                ]
            );
        }
        $this->command->info('Scenario: Created general applications for remaining candidates.');
    }
}
