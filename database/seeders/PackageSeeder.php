<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Question;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Packages...');
        // Create a package
        $package = Package::create([
            'name' => 'General Knowledge - Pack 1',
            'duration' => 60, // in minutes
        ]);

        // Attach questions to the package
        $questions = Question::all();
        $package->questions()->attach($questions->pluck('id'));

        // Create another package
        $package2 = Package::create([
            'name' => 'Mathematics Basics',
            'duration' => 45, // in minutes
        ]);

        // Attach specific questions to the second package
        $mathQuestion = Question::where('question', 'What is 2 + 2?')->first();
        if ($mathQuestion) {
            $package2->questions()->attach($mathQuestion->id);
        }
    }
}
