<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        // The demo deployment is branded as the sample bakery; every other
        // deployment shows the platform brand. Views read these shared values
        // instead of hardcoding "Amal Bakes".
        View::share('brandName', config('app.demo_mode') ? 'أمل بيكنز' : 'دكّاني');
        View::share('brandLatin', config('app.demo_mode') ? 'Amal Bakes' : 'Dukkani');

        // Cap checkout submissions per client IP so a bot cannot flood a store
        // with orders. The GET checkout page is not limited; only the POST.
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
