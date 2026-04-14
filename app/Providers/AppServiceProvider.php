<?php

namespace App\Providers;

use App\Models\FinancialTrade;
use App\Models\Trade;
use App\Models\User;
use App\Policies\FinancialTradePolicy;
use App\Policies\TradePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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

        // Policies
        Gate::policy(Trade::class, TradePolicy::class);
        Gate::policy(FinancialTrade::class, FinancialTradePolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        // Admins bypass all gates
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isAdmin()) {
                return true;
            }
            return null;
        });
    }
}

