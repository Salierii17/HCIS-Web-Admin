<?php

namespace Database\Seeders;

use App\Models\Departments;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Departments...');

        $departmentsData = [
            'Investment Banking',
            'Equity Research',
            'Sales & Trading',
            'Risk Management',
            'Compliance',
            'Technology',
            'Wealth Management',
        ];

        $progressBar = $this->command->getOutput()->createProgressBar(count($departmentsData));
        $progressBar->start();

        foreach ($departmentsData as $deptName) {
            Departments::factory()->create([
                'DepartmentName' => $deptName,
                'ParentDepartment' => null,
            ]);
            $progressBar->advance();
        }
        $progressBar->finish();

        $this->command->info(PHP_EOL . 'Departments created successfully.');
    }
}
