<?php

namespace App\Providers;

use Gate;
use App\Domain\Currency\Repositories\CurrencyRepository;
use App\Domain\Wallet\Policies\WalletPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use RateLimiter;
use App\Domain\Currency\Contracts\CurrencyRepository as ContractsCurrencyRepository;

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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->app->bind(ContractsCurrencyRepository::class, CurrencyRepository::class);

        Gate::define('delete-wallet',  [WalletPolicy::class, 'delete']);
        Gate::define('update-wallet',  [WalletPolicy::class, 'update']);
    }
}
