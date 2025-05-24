<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Package;
use App\Models\Question;
use App\Models\TryOut;
use App\Models\TryoutAnswer;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Auth;


class TryoutOnline extends Component
{
     public $package;
    public $tryOut;
    public $questions;
    public $currentPackageQuestion;
    public $timeLeft;
    public $tryOutAnswers;
    public $selectedAnswers = [];

    public function mount($id) 
    {
        $this->package = Package::with('questions.question.options')->find($id);
        if ($this->package) {
            $this->questions = $this->package->questions;
            if ($this->questions->isNotEmpty()) {
                $this->currentPackageQuestion = $this->questions->first();
            }
        }

        $this->tryOut = TryOut::where('user_id', Auth::id())
                        ->where('package_id', $this->package->id)
                        ->whereNull('finished_at')
                        ->first();
        if (!$this->tryOut) {
            $startedAt = now();
            $durationInSecond = $this->package->duration * 60;

            $this->tryOut = TryOut::create([
                'user_id' => Auth::id(),
                'package_id' => $this->package->id,
                'duration' => $durationInSecond,
                'started_at' => $startedAt
            ]);

            foreach($this->questions as $question) {
                TryOutAnswer::create([
                    'tryout_id' => $this->tryOut->id,
                    'question_id' => $question->question_id,
                    'option_id' => null,
                    'score' => 0
                ]);
            }
        }
        $this->tryOutAnswers = TryOutAnswer::where('tryout_id', $this->tryOut->id)->get();
        foreach($this->tryOutAnswers as $answer) {
            $this->selectedAnswers[$answer->question_id] = $answer->option_id;

        }


        $this->calculateTimeLeft();
    }

    public function render()
    {
        return view('livewire.tryout');
    }

    public function goToQuestion($package_question_id) {
        $this->currentPackageQuestion = $this->questions->where('id', $package_question_id)->first();

        $this->calculateTimeLeft();
    }

     protected function calculateTimeLeft()
    {
        if ($this->tryOut->finished_at) {
            $this->timeLeft = 0;
            
        } else {
            $now = time();
            $startedAt = strtotime($this->tryOut->started_at);
    
            $sisaWaktu = $now - $startedAt;
            if ($sisaWaktu < 0) {
                $this->timeLeft = 0;
            } else {
                $this->timeLeft = max(0, $this->tryOut->duration - $sisaWaktu);
            }
        }

       
    }

    public function saveAnswer($questionId, $optionId)
    {
        $option = QuestionOption::find($optionId);
        $score = $option->score ?? 0;

        $tryOutAnswer = TryoutAnswer::where('tryout_id', $this->tryOut->id)
                                ->where('question_id', $questionId)
                                ->first();
        if ($tryOutAnswer) {
            $tryOutAnswer->update([
                'option_id' => $optionId,
                'score' => $score
            ]);
        }

        $this->tryOutAnswers = TryOutAnswer::where('tryout_id', $this->tryOut->id)->get();

        $this->selectedAnswers[$questionId] = $optionId;

        $this->calculateTimeLeft();
        
    }

    public function submit()
    {
        $this->tryOut->update(['finished_at' => now()]);
        $this->calculateTimeLeft();
        session()->flash('message', 'Data berhasil disimpan');
    }
}

