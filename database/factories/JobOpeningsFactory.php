<?php

namespace Database\Factories;

use App\Models\JobOpenings;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class JobOpeningsFactory extends Factory
{
    protected $model = JobOpenings::class;

    public function definition(): array
    {
        // Generate a job title once to ensure consistency
        $jobTitle = $this->faker->jobTitle();

        return [
            // --- Core Information ---
            'postingTitle' => $jobTitle,
            'JobTitle' => $jobTitle,
            'NumberOfPosition' => $this->faker->numberBetween(1, 5), // A realistic number of openings
            'JobOpeningSystemID' => 'JOB-'.$this->faker->unique()->randomNumber(6), // A unique, formatted ID

            // --- Dates ---
            // These will typically be overridden by the seeder for specific scenarios
            'DateOpened' => Carbon::now(),
            'TargetDate' => Carbon::now()->addDays(30),

            // --- Job Details (Contextual for a Bank) ---
            'Status' => 'New', // A safe default status
            'Industry' => 'Financial Services',
            'Salary' => number_format($this->faker->numberBetween(50000, 150000)), // Formatted salary range
            'JobType' => $this->faker->randomElement(['Permanent', 'Contract', 'Internship']),
            'WorkExperience' => $this->faker->randomElement(['0_1year', '2_3years', '3_5years', '5_7years']),

            // --- Content ---
            'JobDescription' => $this->faker->paragraphs(3, true),
            'JobRequirement' => $this->faker->paragraphs(2, true),
            'JobBenefits' => $this->faker->paragraphs(2, true),

            // --- Location & Remote Status ---
            'Country' => 'Indonesia',
            'State' => $state = $this->faker->randomElement(['West Java', 'DKI Jakarta']),
            'City' => ($state === 'West Java')
                ? $this->faker->randomElement(['Bekasi', 'Bandung', 'Bogor', 'Depok'])
                : $this->faker->randomElement(['Jakarta Selatan', 'Jakarta Pusat', 'Jakarta Barat', 'Jakarta Timur']),
            'ZipCode' => $this->faker->postcode(),
            'RemoteJob' => $this->faker->boolean(20), // 20% chance of being a remote job
            // --- System & Timestamps ---
            'published_career_site' => false, // Default to not published
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
