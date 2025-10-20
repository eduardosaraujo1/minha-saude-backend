<?php

namespace App\Providers;

use App\Data\Services\Google\GoogleService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleService::class,
            function ($app) {
                return new \App\Data\Services\Google\GoogleServiceImpl;
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
