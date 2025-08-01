<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssignTraining;
use App\Notifications\TrainingReminderNotification;
use Illuminate\Support\Carbon;

class SendTrainingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-training-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow()->toDateString(); // format Y-m-d

        $trainings = AssignTraining::with('user', 'package')
            ->whereDate('deadline', $tomorrow)
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($trainings as $training) {
            $user = $training->user;

            if ($user && $user->email) {
                $user->notify(new TrainingReminderNotification($training));

                $training->update([
                    'reminder_sent_at' => now(),
                ]);

                $this->info("Reminder dikirim ke: {$user->email}");
            }
        }

        return Command::SUCCESS;
    }
}
