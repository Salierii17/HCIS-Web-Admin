<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->warn(PHP_EOL.'Creating Questions and Options...');
        // --- Question 1 ---
        $question1 = Question::create([
            'question' => 'What is the capital of France?',
            'explanation' => 'Paris is the capital and most populous city of France.',
        ]);

        // Options for Question 1
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'London', 'score' => 0]);
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'Berlin', 'score' => 0]);
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'Paris', 'score' => 10]);
        QuestionOption::create(['question_id' => $question1->id, 'option_text' => 'Madrid', 'score' => 0]);

        // --- Question 2 ---
        $question2 = Question::create([
            'question' => 'What is 2 + 2?',
            'explanation' => 'The sum of 2 and 2 is 4.',
        ]);

        // Options for Question 2
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => '3', 'score' => 0]);
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => '4', 'score' => 10]);
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => '5', 'score' => 0]);
        QuestionOption::create(['question_id' => $question2->id, 'option_text' => '6', 'score' => 0]);

        // --- Question 3 ---
        $question3 = Question::create([
            'question' => 'Which planet is known as the Red Planet?',
            'explanation' => 'Mars is often referred to as the "Red Planet" because the iron oxide prevalent on its surface gives it a reddish appearance.',
        ]);

        // Options for Question 3
        QuestionOption::create(['question_id' => $question3->id, 'option_text' => 'Earth', 'score' => 0]);
        QuestionOption::create(['question_id' => $question3->id, 'option_text' => 'Mars', 'score' => 10]);
        QuestionOption::create(['question_id' => $question3->id, 'option_text' => 'Jupiter', 'score' => 0]);
        QuestionOption::create(['question_id' => $question3->id, 'option_text' => 'Saturn', 'score' => 0]);
    }
}
