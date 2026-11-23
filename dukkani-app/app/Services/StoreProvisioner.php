<?php

namespace App\Services;

use App\Models\Store;
use App\Models\StoreHour;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates a store together with its owner login and default opening hours.
 * Shared by the `dukkani:store:create` command and the admin panel so both
 * paths produce exactly the same starting state.
 */
class StoreProvisioner
{
    public function __construct(private readonly PlanService $plans) {}

    /**
     * @param  array{store_name: string, slug: string, owner_name: string, email: string, password: string, phone: string, whatsapp?: string|null, city: string, currency: string, country_code: string, timezone: string}  $data
     * @return array{0: User, 1: Store}
     */
    public function create(array $data, bool $startTrial = true): array
    {
        [$user, $store] = DB::transaction(function () use ($data): array {
            $user = User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'email_verified_at' => now(),
            ]);

            $store = Store::create([
                'owner_id' => $user->id,
                'name' => $data['store_name'],
                'slug' => $data['slug'],
                'phone' => $data['phone'],
                'whatsapp' => $data['whatsapp'] ?? null,
                'city' => $data['city'],
                'currency' => $data['currency'],
                'country_code' => $data['country_code'],
                'timezone' => $data['timezone'],
                'status' => 'active',
                'onboarding_completed_at' => now(),
            ]);

            $this->createDefaultHours($store);

            return [$user, $store];
        });

        if ($startTrial) {
            $this->plans->startTrial($store);
        }

        return [$user, $store];
    }

    /**
     * Sunday to Thursday 10:00-20:00, Friday closed, Saturday 11:00-21:00.
     * The owner can change these later from the dashboard.
     */
    protected function createDefaultHours(Store $store): void
    {
        $week = [
            0 => ['10:00:00', '20:00:00', false],
            1 => ['10:00:00', '20:00:00', false],
            2 => ['10:00:00', '20:00:00', false],
            3 => ['10:00:00', '20:00:00', false],
            4 => ['10:00:00', '20:00:00', false],
            5 => [null, null, true],
            6 => ['11:00:00', '21:00:00', false],
        ];

        foreach ($week as $day => [$open, $close, $closed]) {
            StoreHour::create([
                'store_id' => $store->id,
                'day_of_week' => $day,
                'open_time' => $open,
                'close_time' => $close,
                'is_closed' => $closed,
            ]);
        }
    }
}
