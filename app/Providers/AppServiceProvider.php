<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        \Carbon\Carbon::setLocale('fa');

        RateLimiter::for('global', fn (Request $request) => Limit::perMinute(60));

        RateLimiter::for('upload', fn (Request $request) => Limit::perMinute(10));

        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(30));
    }
}
