<?php

namespace App\Http\Middleware;

use App\Models\Store;
use App\Support\Money;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the store for the current request (from the route binding on the
 * storefront, or the authenticated owner on the dashboard) and applies its
 * locale: the currency used by Money::format() and the store's local timezone
 * for dates and opening hours.
 *
 * The timezone is reset to the application default for requests without a
 * store, because Apache/mod_php workers are reused between requests.
 */
class SetStoreContext
{
    protected static ?string $defaultTimezone = null;

    public function handle(Request $request, Closure $next): Response
    {
        $store = $request->route('store');

        if (is_string($store)) {
            $store = Store::where('slug', $store)->first();
        }

        if (! $store instanceof Store) {
            $store = $request->user()?->store;
        }

        if ($store instanceof Store) {
            Money::useCurrency($store->currencyCode());
            $this->useTimezone($store->timezone);
        } else {
            Money::useCurrency(null);
            $this->useTimezone(null);
        }

        return $next($request);
    }

    protected function useTimezone(?string $timezone): void
    {
        self::$defaultTimezone ??= (string) config('app.timezone');

        $timezone = $timezone ?: self::$defaultTimezone;

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }
}
