<?php

namespace App\Http\Middleware;

use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks plan-gated dashboard screens (customer log, analytics) for stores whose
 * plan does not include them. Responds 403 with a friendly upgrade screen so the
 * gate is real but the owner is never stuck at a blank error page.
 */
class EnsurePlanFeature
{
    public function __construct(protected PlanService $plans) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $store = $request->user()?->store;

        if ($store && $this->plans->hasFeature($store, $feature)) {
            return $next($request);
        }

        return response()->view('dashboard.upgrade', [
            'store' => $store,
            'feature' => $feature,
        ], 403);
    }
}
