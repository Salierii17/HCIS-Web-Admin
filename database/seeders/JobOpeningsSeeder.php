<?php

namespace Database\Seeders;

use App\Models\Departments;
use App\Models\JobOpenings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JobOpeningsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Job Openings with various scenarios...');

        // Fetch the Super Admin user to assign as creator/manager
        $adminUser = User::where('name', 'Super Admin')->first();
        if (! $adminUser) {
            $this->command->error('Super Admin user not found. Please ensure the user seeder runs first.');
            return;
        }

        // Fetch all departments
        $departments = Departments::all();
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please ensure the DepartmentsSeeder runs first.');
            return;
        }

        $jobProfiles = [
            // Scenario 1: A job that should already be OPENED and PUBLIC
            [
                'JobTitle' => 'Investment Banking Analyst',
                'Department' => 'Investment Banking',
                'JobType' => 'Permanent',
                'RequiredSkill' => 'Financial Modeling',
                'WorkExperience' => '0_1year',
                'Status' => 'Opened',
                'published_career_site' => 1,
                'DateOpened' => Carbon::now()->subDays(10), // Opened 10 days ago
                'TargetDate' => Carbon::now()->addDays(20), // Closes in 20 days
            ],
            // Scenario 2: A job that is NEW and should be opened by the scheduler
            [
                'JobTitle' => 'Equity Research Associate',
                'Department' => 'Equity Research',
                'JobType' => 'Permanent',
                'RequiredSkill' => 'Valuation',
                'WorkExperience' => '2_3years',
                'Status' => 'New',
                'published_career_site' => 0,
                'DateOpened' => Carbon::now()->subDay(), // Opening date was yesterday
                'TargetDate' => Carbon::now()->addDays(30),
            ],
            // Scenario 3: A job that is NEW and scheduled to open in the FUTURE
            [
                'JobTitle' => 'Compliance Officer',
                'Department' => 'Compliance',
                'JobType' => 'Contract',
                'RequiredSkill' => 'Regulatory Knowledge',
                'WorkExperience' => '5_7years',
                'Status' => 'New',
                'published_career_site' => 0,
                'DateOpened' => Carbon::now()->addDays(7), // Opens in one week
                'TargetDate' => Carbon::now()->addDays(45),
            ],
            // Scenario 4: An OPEN job that has expired and should be CLOSED by the scheduler
            [
                'JobTitle' => 'Lead Cybersecurity Engineer',
                'Department' => 'Technology',
                'JobType' => 'Permanent',
                'RequiredSkill' => 'Network Security',
                'WorkExperience' => '5_7years',
                'Status' => 'Opened',
                'published_career_site' => 1,
                'DateOpened' => Carbon::now()->subDays(30),
                'TargetDate' => Carbon::now()->subHours(5), // Expired 5 hours ago
            ],
            // Scenario 5: A job that is already CLOSED
            [
                'JobTitle' => 'Wealth Management Advisor',
                'Department' => 'Wealth Management',
                'JobType' => 'Permanent',
                'RequiredSkill' => 'Portfolio Management',
                'WorkExperience' => '7_10years',
                'Status' => 'Closed',
                'published_career_site' => 0,
                'DateOpened' => Carbon::now()->subDays(60),
                'TargetDate' => Carbon::now()->subDays(30), // Closed a month ago
            ],
        ];

        $progressBar = $this->command->getOutput()->createProgressBar(count($jobProfiles));
        $progressBar->start();

        foreach ($jobProfiles as $profile) {
            $department = $departments->firstWhere('DepartmentName', $profile['Department']);

            JobOpenings::factory()->create([
                'postingTitle' => $profile['JobTitle'],
                'Department' => $department->id,
                'JobType' => $profile['JobType'],
                'RequiredSkill' => $profile['RequiredSkill'],
                'WorkExperience' => $profile['WorkExperience'],
                'Status' => $profile['Status'],
                'published_career_site' => $profile['published_career_site'],
                'DateOpened' => $profile['DateOpened'],
                'TargetDate' => $profile['TargetDate'],
                'HiringManager' => $adminUser->id,
                'ModifiedBy' => $adminUser->id,
                'CreatedBy' => $adminUser->id,
            ]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->info(PHP_EOL . 'Job Openings created successfully.');

    }
}
