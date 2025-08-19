<?php

namespace App\Providers;

use App\Models\JobOpenings;
use Illuminate\Support\ServiceProvider;

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
