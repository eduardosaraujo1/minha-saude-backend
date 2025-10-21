<?php

namespace App\Providers;

use App\Data\Services\Cache\CacheService;
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

        $this->app->singleton(CacheService::class, function ($app) {
            return new \App\Data\Services\Cache\CacheServiceImpl;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
