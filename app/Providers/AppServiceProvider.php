<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Force HTTPS when running in production (cPanel deployment)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
