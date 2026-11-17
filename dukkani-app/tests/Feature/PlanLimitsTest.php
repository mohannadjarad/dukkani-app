<?php

namespace Tests\Feature;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\PlanService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlanLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected function plans(): PlanService
    {
        return app(PlanService::class);
    }

    protected function makeStore(array $attributes = []): Store
    {
        $owner = User::create([
            'name' => 'مالكة المتجر',
            'email' => Str::random(12).'@example.com',
            'password' => 'secret1234',
        ]);

        return Store::create(array_merge([
            'owner_id' => $owner->id,
            'name' => 'متجر تجريبي',
            'slug' => 'store-'.Str::random(8),
            'phone' => '0599000000',
            'whatsapp' => '970599000000',
            'currency' => 'ILS',
            'country_code' => '970',
            'timezone' => 'Asia/Hebron',
            'status' => 'active',
        ], $attributes));
    }

    protected function makeOrder(Store $store, ?string $status = null): Order
    {
        $customer = Customer::firstOrCreate(
            ['store_id' => $store->id, 'phone' => '0599111222'],
            ['name' => 'زبونة']
        );

        return Order::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'number' => 'AB-'.Str::upper(Str::random(10)),
            'status' => $status ?? OrderStatus::New->value,
            'fulfillment_type' => FulfillmentType::Pickup->value,
            'scheduled_at' => now(),
            'subtotal_fils' => 10000,
            'delivery_fee_fils' => 0,
            'discount_fils' => 0,
            'total_fils' => 10000,
            'payment_method' => PaymentMethod::Cash->value,
            'balance_due_fils' => 10000,
        ]);
    }

    protected function fillMonth(Store $store, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->makeOrder($store);
        }
    }

    protected function makeProduct(Store $store, int $index): Product
    {
        return Product::create([
            'store_id' => $store->id,
            'name' => 'منتج '.$index,
            'slug' => 'product-'.Str::random(10),
            'price_fils' => 5000,
            'is_active' => true,
        ]);
    }

    // Plans and limits -------------------------------------------------------

    public function test_a_free_store_has_fifteen_orders_and_twelve_products(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->assertSame(15, $this->plans()->orderLimit($store));
        $this->assertSame(12, $this->plans()->productLimit($store));
        $this->assertTrue($this->plans()->canAcceptOrder($store));
        $this->assertTrue($this->plans()->canAddProduct($store));
        $this->assertSame('المجانية', $this->plans()->label($store));
    }

    public function test_the_fifteenth_order_is_allowed_and_the_sixteenth_is_blocked(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->fillMonth($store, 14);
        $this->assertSame(14, $this->plans()->monthlyOrders($store));
        $this->assertTrue($this->plans()->canAcceptOrder($store));
        $this->assertTrue($this->plans()->isNearOrderLimit($store));

        $this->makeOrder($store);
        $this->assertSame(15, $this->plans()->monthlyOrders($store));
        $this->assertFalse($this->plans()->canAcceptOrder($store));
        $this->assertTrue($this->plans()->isAtOrderLimit($store));
    }

    public function test_cancelled_orders_do_not_count_towards_the_limit(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->fillMonth($store, 14);
        $this->makeOrder($store, OrderStatus::Cancelled->value);

        $this->assertSame(14, $this->plans()->monthlyOrders($store));
        $this->assertTrue($this->plans()->canAcceptOrder($store));
    }

    public function test_a_store_is_blocked_from_adding_a_thirteenth_product(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        for ($i = 1; $i <= 12; $i++) {
            $this->makeProduct($store, $i);
        }

        $this->assertSame(12, $this->plans()->monthlyProducts($store));
        $this->assertFalse($this->plans()->canAddProduct($store));
        $this->assertTrue($this->plans()->isAtProductLimit($store));
    }

    public function test_pro_is_unlimited(): void
    {
        $store = $this->makeStore([
            'plan' => 'pro',
            'plan_expires_at' => now()->addMonth(),
            'is_trial' => false,
        ]);

        $this->fillMonth($store, 30);

        $this->assertTrue($this->plans()->isUnlimited($store));
        $this->assertNull($this->plans()->orderLimit($store));
        $this->assertNull($this->plans()->productLimit($store));
        $this->assertTrue($this->plans()->canAcceptOrder($store));
    }

    public function test_paid_starter_is_unlimited_for_orders_and_products(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->addMonth(),
            'is_trial' => false,
        ]);

        $this->fillMonth($store, 30);

        for ($i = 1; $i <= 13; $i++) {
            $this->makeProduct($store, $i);
        }

        $this->assertTrue($this->plans()->isUnlimited($store));
        $this->assertNull($this->plans()->orderLimit($store));
        $this->assertNull($this->plans()->productLimit($store));
        $this->assertTrue($this->plans()->canAcceptOrder($store));
        $this->assertTrue($this->plans()->canAddProduct($store));
    }

    public function test_a_trial_store_can_add_a_product_until_it_reaches_twelve(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->addDays(3),
            'is_trial' => true,
        ]);

        for ($i = 1; $i <= 11; $i++) {
            $this->makeProduct($store, $i);
        }

        $this->assertTrue($this->plans()->canAddProduct($store));

        $this->makeProduct($store, 12);

        $this->assertSame(12, $this->plans()->productLimit($store));
        $this->assertFalse($this->plans()->canAddProduct($store));
        $this->assertTrue($this->plans()->isAtProductLimit($store));
    }

    public function test_a_trial_store_cannot_open_the_add_product_screen_at_twelve_products(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->addDays(3),
            'is_trial' => true,
        ]);

        for ($i = 1; $i <= 12; $i++) {
            $this->makeProduct($store, $i);
        }

        $this->actingAs($store->owner)
            ->get(route('products.create'))
            ->assertRedirect(route('products.index'));
    }

    public function test_an_expired_trial_falls_back_to_free_limits_immediately(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->subDay(),
            'is_trial' => true,
        ]);

        $this->assertFalse($this->plans()->isUnlimited($store));
        $this->assertFalse($this->plans()->isOnTrial($store));
        $this->assertSame(15, $this->plans()->orderLimit($store));
        $this->assertSame(12, $this->plans()->productLimit($store));
        $this->assertSame('free', $this->plans()->effectivePlan($store));
    }

    public function test_a_paid_plan_keeps_a_grace_period_after_expiry(): void
    {
        $inGrace = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->subDays(2),
            'is_trial' => false,
        ]);

        $pastGrace = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->subDays(30),
            'is_trial' => false,
        ]);

        $this->assertTrue($this->plans()->isUnlimited($inGrace));
        $this->assertNull($this->plans()->orderLimit($inGrace));

        $this->assertFalse($this->plans()->isUnlimited($pastGrace));
        $this->assertSame(15, $this->plans()->orderLimit($pastGrace));
    }

    public function test_an_admin_order_override_extends_the_free_limit(): void
    {
        $store = $this->makeStore(['plan' => 'free', 'order_limit_override' => 5]);

        $this->assertSame(20, $this->plans()->orderLimit($store));

        $this->fillMonth($store, 15);
        $this->assertTrue($this->plans()->canAcceptOrder($store));
    }

    // Trial and first-month bonus -------------------------------------------

    public function test_a_new_store_starts_on_a_three_day_trial(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->plans()->startTrial($store);
        $store->refresh();

        $this->assertSame('starter', $store->plan);
        $this->assertTrue($store->is_trial);
        $this->assertTrue($this->plans()->isOnTrial($store));
        $this->assertSame(3, $this->plans()->trialDaysLeft($store));
        $this->assertNull($this->plans()->orderLimit($store));
        $this->assertSame(12, $this->plans()->productLimit($store));
    }

    public function test_the_first_paid_activation_adds_the_first_month_bonus(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->plans()->activate($store, 'starter', 1);
        $store->refresh();

        $this->assertSame('starter', $store->plan);
        $this->assertFalse($store->is_trial);
        $this->assertNotNull($store->first_paid_at);
        // One paid month plus the seven-day first-month bonus.
        $this->assertTrue($store->plan_expires_at->between(
            now()->addMonths(1)->addDays(6),
            now()->addMonths(1)->addDays(8),
        ));
    }

    public function test_the_first_month_bonus_is_granted_only_once(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->plans()->activate($store, 'starter', 1);
        $store->refresh();
        $firstPaidAt = $store->first_paid_at->copy();

        $this->plans()->activate($store, 'starter', 1);
        $store->refresh();

        $this->assertTrue($store->first_paid_at->equalTo($firstPaidAt));
        // The second activation is a plain month, no bonus again.
        $this->assertTrue($store->plan_expires_at->between(
            now()->addMonths(1)->subDay(),
            now()->addMonths(1)->addDay(),
        ));
    }

    public function test_activating_free_never_grants_the_first_month_bonus(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->plans()->activate($store, 'free');
        $store->refresh();

        $this->assertNull($store->first_paid_at);
        $this->assertNull($store->plan_expires_at);
    }

    public function test_pro_is_not_purchasable_at_launch(): void
    {
        $this->assertSame(['free', 'starter'], $this->plans()->purchasableKeys());
        $this->assertTrue($this->plans()->isPurchasable('starter'));
        $this->assertFalse($this->plans()->isPurchasable('pro'));
    }

    // Storefront checkout ----------------------------------------------------

    public function test_checkout_is_redirected_to_the_limit_page_once_the_store_is_full(): void
    {
        $store = $this->makeStore(['plan' => 'free']);
        $product = $this->makeProduct($store, 1);
        $this->fillMonth($store, 15);

        $this->post(route('storefront.cart.add', $store), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->get(route('storefront.checkout', $store))
            ->assertRedirect(route('storefront.checkout.limit', $store));

        $before = Order::count();

        $this->post(route('storefront.checkout.store', $store), [
            'name' => 'زبونة',
            'phone' => '0599111222',
            'fulfillment_type' => 'pickup',
            'scheduled_date' => now()->addDays(2)->toDateString(),
            'scheduled_time' => '12:00',
            'payment_method' => 'cash',
            'consent' => '1',
        ])->assertRedirect(route('storefront.checkout.limit', $store));

        $this->assertSame($before, Order::count());
    }

    public function test_the_limit_page_offers_whatsapp_to_the_store_and_to_support(): void
    {
        $store = $this->makeStore(['plan' => 'free']);
        $this->fillMonth($store, 15);

        $this->get(route('storefront.checkout.limit', $store))
            ->assertOk()
            ->assertSee('المتجر وصل حده الشهري')
            ->assertSee('اطلبي عبر واتساب المتجر')
            ->assertSee('تواصلي مع')
            ->assertSee('wa.me');
    }

    public function test_the_limit_page_keeps_the_support_number_international_for_another_country(): void
    {
        config(['plans.support.whatsapp' => '972569991081']);

        $store = $this->makeStore([
            'plan' => 'free',
            'country_code' => '20',
            'currency' => 'EGP',
            'phone' => '01000000000',
            'timezone' => 'Africa/Cairo',
        ]);
        $this->fillMonth($store, 15);

        $this->get(route('storefront.checkout.limit', $store))
            ->assertOk()
            ->assertSee('https://wa.me/972569991081', false)
            ->assertDontSee('wa.me/20569991081', false);
    }

    public function test_the_dashboard_upgrade_link_uses_the_support_number(): void
    {
        config(['plans.support.whatsapp' => '972569991081']);

        $store = $this->makeStore(['plan' => 'free']);

        $this->actingAs($store->owner)
            ->get(route('settings.index', ['tab' => 'plan']))
            ->assertOk()
            ->assertSee('https://wa.me/972569991081', false)
            ->assertSee('رقّي عبر واتساب');
    }

    public function test_the_limit_page_redirects_back_when_the_store_can_accept_orders(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->get(route('storefront.checkout.limit', $store))
            ->assertRedirect(route('storefront.checkout', $store));
    }

    // Product add gate -------------------------------------------------------

    public function test_the_add_product_screen_redirects_when_the_limit_is_reached(): void
    {
        $store = $this->makeStore(['plan' => 'free']);
        $owner = $store->owner;

        for ($i = 1; $i <= 11; $i++) {
            $this->makeProduct($store, $i);
        }

        $this->actingAs($owner)
            ->get(route('products.create'))
            ->assertOk();

        $this->makeProduct($store, 12);

        $this->actingAs($owner)
            ->get(route('products.create'))
            ->assertRedirect(route('products.index'));
    }

    public function test_a_thirteenth_product_cannot_be_stored(): void
    {
        $store = $this->makeStore(['plan' => 'free']);
        $owner = $store->owner;

        for ($i = 1; $i <= 12; $i++) {
            $this->makeProduct($store, $i);
        }

        $this->actingAs($owner)
            ->post(route('products.store'), [
                'name' => 'منتج زائد',
                'price' => '5',
            ])
            ->assertSessionHasErrors('name');

        $this->assertSame(12, $store->products()->count());
    }

    // Free plan feature gates ------------------------------------------------

    public function test_customers_and_analytics_are_locked_on_the_free_plan(): void
    {
        $store = $this->makeStore(['plan' => 'free']);

        $this->actingAs($store->owner)->get(route('analytics.index'))
            ->assertStatus(403)
            ->assertSee('متاحة في خطة Starter');

        $this->actingAs($store->owner)->get(route('customers.index'))
            ->assertStatus(403)
            ->assertSee('متاحة في خطة Starter');
    }

    public function test_customers_and_analytics_open_on_starter(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->addMonth(),
            'is_trial' => false,
        ]);

        $this->actingAs($store->owner)->get(route('analytics.index'))->assertOk();
        $this->actingAs($store->owner)->get(route('customers.index'))->assertOk();
    }

    // Dashboard banner -------------------------------------------------------

    public function test_the_trial_banner_and_countdown_are_shown(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->addDays(7),
            'is_trial' => true,
        ]);

        $this->actingAs($store->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Starter مجاناً')
            ->assertSee('باقي 7 أيام');
    }

    public function test_the_trial_banner_disappears_after_expiry(): void
    {
        $store = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->subDay(),
            'is_trial' => true,
        ]);

        $this->actingAs($store->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Starter مجاناً');
    }

    public function test_the_warning_banner_appears_near_the_limit_and_the_red_banner_at_the_limit(): void
    {
        $store = $this->makeStore(['plan' => 'free']);
        $this->fillMonth($store, 12);

        $this->actingAs($store->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('استعملتِ 12 من 15 طلباً');

        $this->fillMonth($store, 3);

        $this->actingAs($store->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('وصلتِ 15/15');
    }

    // Expiry command ---------------------------------------------------------

    public function test_the_expire_command_returns_lapsed_plans_to_free(): void
    {
        $trial = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->subDay(),
            'is_trial' => true,
        ]);

        $paidPastGrace = $this->makeStore([
            'plan' => 'pro',
            'plan_expires_at' => now()->subDays(30),
            'is_trial' => false,
        ]);

        $paidStillActive = $this->makeStore([
            'plan' => 'starter',
            'plan_expires_at' => now()->addDays(10),
            'is_trial' => false,
        ]);

        $this->artisan('plans:expire')->assertSuccessful();

        $this->assertSame('free', $trial->refresh()->plan);
        $this->assertNull($trial->plan_expires_at);
        $this->assertFalse($trial->is_trial);

        $this->assertSame('free', $paidPastGrace->refresh()->plan);
        $this->assertSame('starter', $paidStillActive->refresh()->plan);
    }

    // Demo separation --------------------------------------------------------

    public function test_the_demo_store_is_kept_on_pro_for_the_showcase(): void
    {
        $this->seed(DemoSeeder::class);

        $store = Store::where('slug', 'amal-bakes')->firstOrFail();

        $this->assertSame('pro', $store->plan);
        $this->assertTrue($this->plans()->isUnlimited($store));
    }
}
