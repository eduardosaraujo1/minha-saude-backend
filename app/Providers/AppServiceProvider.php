<?php

namespace App\Providers;

use App\Modules\Document\Services\Adapters\LocalFileStorageAdapter;
use App\Modules\Document\Services\Ports\FileStoragePort;
use App\Modules\User\Services\Adapters\CacheServiceAdapter;
use App\Modules\User\Services\Adapters\GoogleServiceAdapter;
use App\Modules\User\Services\Ports\CacheServicePort;
use App\Modules\User\Services\Ports\GoogleServicePort;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GoogleServicePort::class,
            function ($app) {
                return new GoogleServiceAdapter;
            }
        );

        $this->app->singleton(CacheServicePort::class, function ($app) {
            return new CacheServiceAdapter;
        });

        $this->app->singleton(FileStoragePort::class, function ($app) {
            return new LocalFileStorageAdapter;
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
