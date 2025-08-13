<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Departments;
use App\Models\User;
use Database\Seeders\concerns\ProgressBarConcern;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use ProgressBarConcern;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('permission:cache-reset');

        // Generate all permissions from your code
        $this->command->warn(PHP_EOL . 'Generating permissions...');
        Artisan::call('shield:generate --all');
        $this->command->info('Permissions generated.');

        // Define roles and their specific permissions
        $rolePermissions = $this->defineRolePermissions();

        // Create roles and sync permissions
        $this->command->warn(PHP_EOL . 'Creating roles and assigning permissions...');
        foreach ($rolePermissions as $roleName => $permissions) {
            // Use updateOrCreate to make the seeder re-runnable
            $role = Role::updateOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );

            // Use syncPermissions to assign ONLY the permissions in the array
            $role->syncPermissions($permissions);
            $this->command->info("Role '{$roleName}' created/updated.");
        }
        $this->command->info('Roles and permissions synced.');

        // Create Users and Assign Roles
        $this->createUsers();

        // Seed other application data
        $this->seedApplicationData();

        $this->command->info(PHP_EOL . 'Database seeding completed successfully.');

    }

    /**
     * Defines the permission map for each role.
     * This is the new "source of truth".
     */
    private function defineRolePermissions(): array
    {
        $standardViewResources = [
            'Material',
            'Package',
            'Tryout',
        ];
        $standardPermissions = [];
        foreach ($standardViewResources as $resource) {
            $resourceName = Str::snake($resource);
            $standardPermissions[] = 'view_' . $resourceName;
            $standardPermissions[] = 'view_any_' . $resourceName;
        }

        return [
            'super_admin' => Permission::pluck('name')->all(),

            'Administrator' => Permission::where('name', 'not like', '%impersonate%')
                ->pluck('name')
                ->all(),

            'Standard' => $standardPermissions,
        ];
    }

    /**
     * Creates the admin user and standard users.
     */
    private function createUsers(): void
    {
        $this->command->warn(PHP_EOL . 'Creating users...');

        // Super Admin User
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin.user@gmail.com',
            'password' => 'password',
        ]);
        $superAdmin->assignRole('super_admin');
        $this->command->info('Super Admin user created.');

        // Standard Users
        $this->call([UserSeeder::class]);
        User::where('email', '!=', 'superadmin.user@gmail.com')->get()->each(function ($user) {
            $user->assignRole('Standard');
        });
        $this->command->info('Standard users created and role assigned.');
    }

    /**
     * Calls all other application-specific seeders.
     */
    private function seedApplicationData(): void
    {
        // --- RECRUITMENT ---
        // Departments & Job Openings
        $this->command->warn(PHP_EOL . 'Seeding Departments and Job Openings...');
        $this->call([
            // DepartmentsSeeder::class,
            // JobOpeningsSeeder::class,
        ]);
        $this->command->info('Departments and Job Openings data seeded.');

        // JobCandidate, Candidate, and Referral
        $this->command->warn(PHP_EOL . 'Seeding JobCandidate, Candidate, and Referral...');
        $this->call([
            // CandidateSeeder::class,
            // JobCandidateSeeder::class,
            // ReferralSeeder::class,
        ]);
        $this->command->info('JobCandidate, Candidate, and Referral data seeded.');

        // --- TRAINING ---
        $this->command->warn(PHP_EOL . 'Seeding Training data...');
        $this->call([
            MaterialSeeder::class,
            TrainingSeeder::class,
            AssignTrainingSeeder::class,
            // TryoutSeeder::class,
        ]);

        // --- Attendance ---
        $this->command->warn(PHP_EOL . 'Seeding Attendance Status and Attendance Records...');
        $this->call([
            AttendanceStatusSeeder::class,
            WorkArrangementSeeder::class,
            AttendanceSeeder::class,
            AttendanceApprovalSeeder::class,
        ]);
        $this->command->info('Attendance data seeded.');
    }
}
