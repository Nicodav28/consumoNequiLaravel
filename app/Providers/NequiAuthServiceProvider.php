<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\NequiAuthService;

class NequiAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(NequiAuthService::class, function () {
            return new NequiAuthService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
