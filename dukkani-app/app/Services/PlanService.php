<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Carbon;

/**
 * Reads the subscription plan config and answers the questions the rest of the
 * app asks about a store: what its limits are, how much it has used this month,
 * whether it may accept another order or product, and whether a feature is on.
 *
 * Expiry is enforced in real time here (a plan stops counting as unlimited the
 * moment it lapses, plus the paid grace period); the `plans:expire` command is
 * only a daily cleanup that rewrites the stored plan back to Free.
 */
class PlanService
{
    /** @return array<string, array<string, mixed>> */
    public function plans(): array
    {
        return (array) config('plans.plans', []);
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return array_keys($this->plans());
    }

    /**
     * Plans that may actually be sold/assigned right now. Pro is on the
     * roadmap only, so it is excluded until its features ship.
     *
     * @return array<int, string>
     */
    public function purchasableKeys(): array
    {
        return array_keys(array_filter(
            $this->plans(),
            fn (array $plan) => ($plan['purchasable'] ?? true) !== false,
        ));
    }

    public function isPurchasable(string $plan): bool
    {
        return in_array($plan, $this->purchasableKeys(), true);
    }

    /** @return array<string, mixed> */
    public function config(?string $plan): array
    {
        $plans = $this->plans();

        if ($plan !== null && array_key_exists($plan, $plans)) {
            return (array) $plans[$plan];
        }

        return (array) ($plans[config('plans.default', 'free')] ?? []);
    }

    /** The plan stored on the row, without any expiry logic. */
    public function storedPlan(Store $store): string
    {
        $plan = $store->plan ?: (string) config('plans.default', 'free');

        return array_key_exists($plan, $this->plans()) ? $plan : 'free';
    }

    /** The plan a store is actually entitled to right now. */
    public function effectivePlan(Store $store): string
    {
        $plan = $this->storedPlan($store);

        if (in_array($plan, ['starter', 'pro'], true) && $this->isUnlimited($store)) {
            return $plan;
        }

        return in_array($plan, ['starter', 'pro'], true) ? 'free' : $plan;
    }

    /** @return array<string, mixed> */
    public function plan(Store $store): array
    {
        return $this->config($this->effectivePlan($store));
    }

    public function label(Store $store): string
    {
        return (string) ($this->plan($store)['label'] ?? 'المجانية');
    }

    /**
     * A store is unlimited when it is on Starter/Pro and the plan has not
     * lapsed (or, for paid plans, is still inside the grace window).
     */
    public function isUnlimited(Store $store): bool
    {
        $plan = $this->storedPlan($store);

        if (! in_array($plan, ['starter', 'pro'], true)) {
            return false;
        }

        $expires = $store->plan_expires_at;

        if ($expires === null || $expires->isFuture()) {
            return true;
        }

        // Paid plans keep working through the grace period; trials do not.
        if (! $store->is_trial) {
            return $expires->copy()
                ->addDays((int) config('plans.grace_days', 0))
                ->isFuture();
        }

        return false;
    }

    public function isOnTrial(Store $store): bool
    {
        return (bool) $store->is_trial
            && $store->plan_expires_at !== null
            && $store->plan_expires_at->isFuture();
    }

    public function trialDaysLeft(Store $store): int
    {
        if (! $this->isOnTrial($store)) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()
            ->diffInDays($store->plan_expires_at->copy()->startOfDay(), false));
    }

    /** Order ceiling for this month, or null when unlimited. */
    public function orderLimit(Store $store): ?int
    {
        if ($this->isUnlimited($store)) {
            return null;
        }

        $base = $this->plan($store)['order_limit'] ?? null;

        if ($base === null) {
            return null;
        }

        return (int) $base + max(0, (int) ($store->order_limit_override ?? 0));
    }

    /** Product ceiling, or null when the plan has no product cap. */
    public function productLimit(Store $store): ?int
    {
        // Orders may be unlimited while products still have a cap (Starter),
        // so read the product limit from the effective plan directly.
        $limit = $this->plan($store)['product_limit'] ?? null;

        return $limit === null ? null : (int) $limit;
    }

    /** Orders created in the store's current month, every status but cancelled. */
    public function monthlyOrders(Store $store): int
    {
        return $this->usingStoreTimezone($store, fn () => Order::forStore($store)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count());
    }

    public function monthlyProducts(Store $store): int
    {
        return Product::where('store_id', $store->id)->count();
    }

    public function canAcceptOrder(Store $store): bool
    {
        $limit = $this->orderLimit($store);

        return $limit === null || $this->monthlyOrders($store) < $limit;
    }

    public function canAddProduct(Store $store): bool
    {
        $limit = $this->productLimit($store);

        return $limit === null || $this->monthlyProducts($store) < $limit;
    }

    /** True once the month's order ceiling has been reached (red badge). */
    public function isAtOrderLimit(Store $store): bool
    {
        $limit = $this->orderLimit($store);

        return $limit !== null && $this->monthlyOrders($store) >= $limit;
    }

    /** True just before the ceiling is reached (warning banner). */
    public function isNearOrderLimit(Store $store): bool
    {
        $limit = $this->orderLimit($store);

        if ($limit === null) {
            return false;
        }

        $used = $this->monthlyOrders($store);

        return $used < $limit && ($limit - $used) <= (int) config('plans.warning_remaining', 3);
    }

    public function isAtProductLimit(Store $store): bool
    {
        $limit = $this->productLimit($store);

        return $limit !== null && $this->monthlyProducts($store) >= $limit;
    }

    public function hasFeature(Store $store, string $feature): bool
    {
        return (bool) ($this->plan($store)['features'][$feature] ?? false);
    }

    /**
     * Pro features that are still being built, shown with a "قريباً" badge.
     * Platform-level: every store sees the full roadmap list.
     *
     * @return array<int, string>
     */
    public function comingSoon(): array
    {
        return (array) config('plans.plans.pro.coming_soon', []);
    }

    /**
     * Usage snapshot used by the dashboard banner and the plan screen.
     *
     * @return array{limit: int|null, used: int, remaining: int|null, at_limit: bool, near_limit: bool, unlimited: bool, plan: string, label: string, trial: bool, trial_days: int, expires_at: Carbon|null}
     */
    public function ordersUsage(Store $store): array
    {
        $limit = $this->orderLimit($store);
        $used = $this->monthlyOrders($store);

        return [
            'limit' => $limit,
            'used' => $used,
            'remaining' => $limit === null ? null : max(0, $limit - $used),
            'at_limit' => $limit !== null && $used >= $limit,
            'near_limit' => $limit !== null && $used < $limit && ($limit - $used) <= (int) config('plans.warning_remaining', 3),
            'unlimited' => $limit === null,
            'plan' => $this->effectivePlan($store),
            'label' => $this->label($store),
            'trial' => $this->isOnTrial($store),
            'trial_days' => $this->trialDaysLeft($store),
            'expires_at' => $store->plan_expires_at,
        ];
    }

    /**
     * Activate a paid plan for a number of months (null = no expiry). Clearing
     * the order override avoids carrying an old courtesy allowance forward.
     *
     * The first paid activation for a store also adds the first-month bonus
     * days on top of the paid period, granted once (tracked by first_paid_at).
     */
    public function activate(Store $store, string $plan, ?int $months = null): Store
    {
        $plan = $this->config($plan) === [] ? 'free' : $plan;

        $expires = $months ? now()->addMonths($months) : null;
        $firstPaidAt = $store->first_paid_at;

        if ($plan !== 'free' && $months && $firstPaidAt === null) {
            $bonus = (int) config('plans.first_month_bonus_days', 0);

            if ($bonus > 0 && $expires !== null) {
                $expires->addDays($bonus);
            }

            $firstPaidAt = now();
        }

        $store->forceFill([
            'plan' => $plan,
            'plan_expires_at' => $expires,
            'is_trial' => false,
            'order_limit_override' => null,
            'first_paid_at' => $firstPaidAt,
        ])->save();

        return $store;
    }

    /** Put a brand-new store on the free full-feature Starter trial. */
    public function startTrial(Store $store, ?int $days = null): Store
    {
        $store->forceFill([
            'plan' => 'starter',
            'plan_expires_at' => now()->addDays($days ?? (int) config('plans.trial_days', 3)),
            'is_trial' => true,
        ])->save();

        return $store;
    }

    public function grantOrderOverride(Store $store, ?int $extra): Store
    {
        $store->forceFill([
            'order_limit_override' => ($extra === null || $extra <= 0) ? null : $extra,
        ])->save();

        return $store;
    }

    /**
     * Run a callback with the app timezone switched to the store's, so calendar
     * boundaries ("this month") match the store's local clock. Mirrors
     * SetStoreContext, which does the same for web requests.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function usingStoreTimezone(Store $store, callable $callback)
    {
        $timezone = $store->timezone ?: (string) config('app.timezone');
        $original = (string) config('app.timezone');

        if ($timezone === $original) {
            return $callback();
        }

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        try {
            return $callback();
        } finally {
            config(['app.timezone' => $original]);
            date_default_timezone_set($original);
        }
    }
}
