<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\JobOpenings;
use App\Observers\JobOpeningObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        JobOpenings::observe(\App\Observers\JobOpeningObserver::class);
    }

    // public function boot(): void
    // {
    //     //
    // }
}
