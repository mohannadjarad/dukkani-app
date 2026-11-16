<?php

namespace App\Http\Middleware;

use App\Models\Store;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hidden when a store is suspended from the admin panel: its storefront pages
 * return 404 instead of serving a shop that is no longer active.
 */
class EnsureStoreActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = $request->route('store');

        if (is_string($store)) {
            $store = Store::where('slug', $store)->first();
        }

        abort_unless($store instanceof Store && $store->status === 'active', 404);

        return $next($request);
    }
}
