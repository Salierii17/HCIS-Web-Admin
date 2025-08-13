<?php

namespace Database\Seeders;

use App\Models\Candidates;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CandidateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Candidates with structured, realistic data...');
        $faker = Faker::create('id_ID');

        $jobTitles = [
            'Analis Keuangan',
            'Manajer Investasi',
            'Relationship Manager',
            'Staf Kepatuhan (Compliance)',
            'Auditor IT',
            'Back End Developer',
            'Data Scientist',
            'Analis Risiko',
            'Sales KPR',
            'Account Officer',
            'Teller',
            'Customer Service',
            'Treasury Staff',
            'Equity Sales',
            'Fixed Income Analyst',
            'Product Manager (Fintech)',
            'UI/UX Designer',
            'DevOps Engineer',
        ];

        for ($i = 0; $i < 50; $i++) {
            // --- Generate structured data for Repeater fields ---

            // 1. SkillSet
            $skills = [];
            $skillCount = rand(1, 4);
            $skillNames = ['Financial Modeling', 'Valuation', 'Python', 'Regulatory Compliance', 'Portfolio Management', 'Communication', 'SQL', 'Java'];
            for ($j = 0; $j < $skillCount; $j++) {
                $skills[] = [
                    'skill' => $faker->randomElement($skillNames),
                    'proficiency' => $faker->randomElement(['Master', 'Intermediate', 'Beginner']),
                    'experience' => $faker->randomElement(['1year', '2year', '3year', '5year', '10year+']),
                    'last_used' => Carbon::now()->subYears(rand(0, 5))->year,
                ];
            }

            // 2. School
            $schools = [];
            $schoolCount = rand(1, 2);
            for ($j = 0; $j < $schoolCount; $j++) {
                $schools[] = [
                    'school_name' => 'Universitas '.$faker->city,
                    'major' => $faker->randomElement(['Manajemen Keuangan', 'Teknik Informatika', 'Hukum Bisnis', 'Akuntansi']),
                    'duration' => $faker->randomElement(['4years', '5years']),
                    'pursuing' => $faker->boolean(10), // 10% chance of being true
                ];
            }

            // 3. Experience Details
            $experiences = [];
            $expCount = rand(1, 3);
            for ($j = 0; $j < $expCount; $j++) {
                $experiences[] = [
                    'current' => ($j === 0), // First one is the current job
                    'company_name' => $faker->company,
                    'duration' => $faker->randomElement(['1year', '2year', '3year']),
                    'role' => $faker->randomElement($jobTitles),
                    'company_address' => $faker->address,
                ];
            }

            Candidates::create([
                'FirstName' => $faker->firstName,
                'LastName' => $faker->lastName,
                'email' => $faker->unique()->safeEmail,
                'Mobile' => $faker->phoneNumber,
                'ExperienceInYears' => $faker->numberBetween(0, 15),
                'CurrentJobTitle' => $faker->randomElement($jobTitles),
                'ExpectedSalary' => $faker->numberBetween(10, 50) * 1000000,
                'HighestQualificationHeld' => $faker->randomElement(['S1 Akuntansi', 'S2 Manajemen Keuangan', 'S1 Teknik Informatika', 'S1 Hukum']),
                'CurrentEmployer' => $faker->company,
                'City' => $faker->city,
                'Country' => 'Indonesia',
                'State' => $faker->state,

                // --- Store the structured data as JSON ---
                'SkillSet' => $skills,
                'School' => $schools,
                'ExperienceDetails' => $experiences,
            ]);
        }
        $this->command->info('Candidates created successfully.');
    }
}
