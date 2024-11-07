<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function boot(): void
    {
        DB::listen(function ($query) {
            // This will measure the time it took for the query to execute in milliseconds
            $executionTime = $query->time;

            // Log the query execution time
            Log::info('Query executed in ' . $executionTime . ' ms: ' . $query->sql);
        });
    }
}
