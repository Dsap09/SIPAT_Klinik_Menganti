<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
        Carbon::setLocale(config('app.locale'));

        RateLimiter::for('pendaftaran', fn (Request $request) => Limit::perMinute(
            (int) config('sipat.throttle.pendaftaran')
        )->by($request->ip()));

        RateLimiter::for('status-antrean', fn (Request $request) => Limit::perMinute(
            (int) config('sipat.throttle.status')
        )->by($request->ip()));
    }
}
