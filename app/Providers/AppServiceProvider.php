<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * DI/composition mechanism (di pillar). Register()/boot() are intentionally
 * empty - this proves the service-container wiring exists and runs, without
 * binding any domain-specific interface or implementation. Backlog items
 * add their own bindings here as they need them.
 */
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
        //
    }
}
