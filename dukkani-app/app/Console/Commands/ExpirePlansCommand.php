<?php

namespace App\Console\Commands;

use App\Models\Store;
use Illuminate\Console\Command;

/**
 * Daily cleanup that rewrites lapsed plans back to Free. Enforcement already
 * happens in real time inside PlanService, so this command only keeps the
 * stored plan column honest (for dashboards and the admin list).
 */
class ExpirePlansCommand extends Command
{
    protected $signature = 'plans:expire';

    protected $description = 'Return stores whose plan or trial has lapsed back to the Free limits';

    public function handle(): int
    {
        $grace = (int) config('plans.grace_days', 0);

        $free = [
            'plan' => 'free',
            'plan_expires_at' => null,
            'is_trial' => false,
            'order_limit_override' => null,
        ];

        $trials = Store::where('is_trial', true)
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<=', now())
            ->update($free);

        $paid = Store::where('is_trial', false)
            ->whereIn('plan', ['starter', 'pro'])
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<=', now()->subDays($grace))
            ->update($free);

        $this->components->info("Reverted to Free: {$trials} trial, {$paid} paid.");

        return self::SUCCESS;
    }
}
