<?php

namespace Database\Seeders;

use App\Models\AssignTraining;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssignTrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Assigning Trainings to Users...');

        $users = User::all()->take(5);
        $packages = Package::all();

        if ($users->count() < 5 || $packages->isEmpty()) {
            $this->command->error('Not enough users or packages to seed all scenarios. Please seed more users/packages first.');

            return;
        }

        // --- Scenario 1: New Hire Onboarding ---
        // A new user is assigned a fundamental training with a deadline in 2 weeks.
        $newHire = $users->get(1);
        $ibPackage = Package::where('name', 'Investment Banking Fundamentals')->first();
        if ($newHire && $ibPackage) {
            AssignTraining::create([
                'user_id' => $newHire->id,
                'package_id' => $ibPackage->id,
                'deadline' => Carbon::now()->addWeeks(2),
                'reminder_sent_at' => null,
            ]);
            $this->command->info("Scenario 1: Assigned onboarding training to User ID: {$newHire->id}.");
        }

        // --- Scenario 2: Upcoming Deadline ---
        // An existing user has a training deadline in 3 days.
        $urgentUser = $users->get(2);
        $compliancePackage = Package::where('name', 'Financial Compliance & Regulation')->first();
        if ($urgentUser && $compliancePackage) {
            AssignTraining::create([
                'user_id' => $urgentUser->id,
                'package_id' => $compliancePackage->id,
                'deadline' => Carbon::now()->addDays(3),
                'reminder_sent_at' => Carbon::now()->subDay(), // Reminder was sent yesterday
            ]);
            $this->command->info("Scenario 2: Assigned training with upcoming deadline to User ID: {$urgentUser->id}.");
        }

        // --- Scenario 3: Overdue Training ---
        // A user's training deadline was last week.
        $overdueUser = $users->get(3);
        $techPackage = Package::where('name', 'Financial Technology & Security')->first();
        if ($overdueUser && $techPackage) {
            AssignTraining::create([
                'user_id' => $overdueUser->id,
                'package_id' => $techPackage->id,
                'deadline' => Carbon::now()->subWeek(),
                'reminder_sent_at' => Carbon::now()->subDays(8),
            ]);
            $this->command->info("Scenario 3: Created an overdue training assignment for User ID: {$overdueUser->id}.");
        }

        // --- Scenario 4: Historical Completed Assignment ---
        // A training that was assigned and completed a month ago.
        $veteranUser = $users->get(4);
        $erPackage = Package::where('name', 'Equity Research & Valuation')->first();
        if ($veteranUser && $erPackage) {
            AssignTraining::create([
                'user_id' => $veteranUser->id,
                'package_id' => $erPackage->id,
                'deadline' => Carbon::now()->subWeeks(3),
                'created_at' => Carbon::now()->subMonth(),
                'updated_at' => Carbon::now()->subMonth(),
                // Note: We assume completion is tracked elsewhere (e.g., in the 'tryouts' table)
            ]);
            $this->command->info("Scenario 4: Created a historical training assignment for User ID: {$veteranUser->id}.");
        }

        // --- Scenario 5: Multiple Assignments for one User ---
        // A high-achiever is assigned both compliance and tech trainings.
        $proUser = $users->get(0); // Use the main test user
        if ($proUser && $compliancePackage && $techPackage) {
            AssignTraining::create([
                'user_id' => $proUser->id,
                'package_id' => $compliancePackage->id,
                'deadline' => Carbon::now()->addMonth(),
            ]);
            AssignTraining::create([
                'user_id' => $proUser->id,
                'package_id' => $techPackage->id,
                'deadline' => Carbon::now()->addMonth(),
            ]);
            $this->command->info("Scenario 5: Assigned multiple trainings to User ID: {$proUser->id}.");
        }
    }
}
