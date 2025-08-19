<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Question;
use App\Models\Tryout;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TryoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Tryouts and Answers...');

        // Get a random user and a random package to create a tryout
        $user = User::inRandomOrder()->first();
        $package = Package::inRandomOrder()->first();

        if ($user && $package) {
            // Create a completed tryout instance
            $tryout = Tryout::create([
                'user_id' => $user->id,
                'package_id' => $package->id,
                'duration' => $package->duration,
                'started_at' => Carbon::now()->subMinutes($package->duration),
                'finished_at' => Carbon::now(),
            ]);

            // FIX: To prevent issues with pivot model access, we explicitly get the Question models.
            // 1. Get the IDs of the questions associated with the package.
            $questionIds = $package->questions()->pluck('questions.id');
            // 2. Fetch the full Question models with their options.
            $questions = Question::with('options')->findMany($questionIds);

            // 3. Seed answers for the tryout using the correct Question models.
            foreach ($questions as $question) {
                $option = $question->options()->inRandomOrder()->first();

                if ($option) {
                    $tryout->answers()->create([
                        'question_id' => $question->id,
                        'option_id' => $option->id,
                        'score' => $option->score,
                    ]);
                }
            }
            $this->command->info("Created a tryout for User ID: {$user->id} with Package ID: {$package->id}");
        } else {
            $this->command->error('Could not create a tryout. No users or packages found.');
        }

        // Create another tryout that is still in progress
        $user2 = User::where('id', '!=', $user->id ?? 0)->inRandomOrder()->first();
        $package2 = Package::inRandomOrder()->first();

        if ($user2 && $package2) {
            Tryout::create([
                'user_id' => $user2->id,
                'package_id' => $package2->id,
                'duration' => $package2->duration,
                'started_at' => Carbon::now()->subMinutes(10), // Started 10 minutes ago
                'finished_at' => null, // Not finished yet
            ]);
            $this->command->info("Created an in-progress tryout for User ID: {$user2->id} with Package ID: {$package2->id}");
        }
    }
}
