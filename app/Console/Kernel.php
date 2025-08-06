<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('app:send-training-reminders')->dailyAt('20:00');
        
        $schedule->command('attendance:daily-verify')->dailyAt('01:27')
        ->appendOutputTo(storage_path('logs/scheduler.log'));
        
        // For development - runs every minute
        if (app()->environment('local')) {
            $schedule->command('job-openings:manage-status')
                    ->everyMinute();
        }
        
        // For production - runs hourly at :00
        // $schedule->command('job-openings:manage-status')
        //         ->hourly()
        //         ->withoutOverlapping();

        //---------

        // $schedule->command('job-openings:manage-status')->everyMinute(); // For testing
        // For production, use ->hourly() or ->dailyAt('03:00')

        // $schedule->command('job-openings:manage-status')
        //  ->hourly() // Or ->dailyAt('03:00') for once per day
        //  ->onOneServer() // If using multiple servers
        //  ->withoutOverlapping(); // Prevent overlapping runs
    }

    // protected function schedule(Schedule $schedule)
    // {
    //     $schedule->command('job-openings:manage-status')->everyMinute(); // For testing, you might want to run more frequently
    //     // For production, you might want to use ->everyFiveMinutes() or ->hourly()
    // }

    // protected function schedule(Schedule $schedule)
    // {
    //     $schedule->command('job-openings:close-expired')->daily();
    // }

    // protected function schedule(Schedule $schedule): void
    // {
    //     // $schedule->command('inspire')->hourly();
    // }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

    // protected function commands(): void
    // {
    //     $this->load(__DIR__.'/Commands');

    //     require base_path('routes/console.php');
    // }

    protected $commands = [
        \App\Console\Commands\ManageJobOpeningStatus::class,
    ];
}
