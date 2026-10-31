<?php

namespace Tests\Feature;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use App\Models\User;
use App\Services\AnalyticsService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
        $this->owner = $this->store->owner;
    }

    public function test_guests_are_redirected_from_analytics(): void
    {
        $this->get('/dashboard/analytics')->assertRedirect('/login');
    }

    public function test_the_analytics_page_renders_for_the_owner(): void
    {
        $this->actingAs($this->owner)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('التحليلات')
            ->assertSee('إيرادات الفترة')
            ->assertSee('حركة الإيرادات')
            ->assertSee('حالة الطلبات')
            ->assertSee('الأكثر مبيعاً')
            ->assertSee('أيام الذروة')
            ->assertSee('أفضل العملاء')
            ->assertSee('طرق الدفع');
    }

    public function test_the_analytics_period_accepts_supported_ranges_and_falls_back(): void
    {
        $this->actingAs($this->owner)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('الطلبات المسلّمة خلال 30 يوماً');

        foreach ([7 => '7 أيام', 90 => '90 يوماً'] as $period => $label) {
            $this->actingAs($this->owner)
                ->get(route('analytics.index', ['period' => $period]))
                ->assertOk()
                ->assertSee('الطلبات المسلّمة خلال '.$label);
        }

        $this->actingAs($this->owner)
            ->get(route('analytics.index', ['period' => 13]))
            ->assertOk()
            ->assertSee('الطلبات المسلّمة خلال 30 يوماً');
    }

    public function test_period_summary_is_scoped_to_the_store_and_date_range(): void
    {
        [$store, $customer] = $this->makeStoreWithCustomer('analytics-summary');

        $delivered = $this->makeOrder($store, $customer, ['total_fils' => 20000]);
        $this->setCreatedAt($delivered, now()->subDays(2));
        $delivered->items()->create([
            'product_name' => 'كيكة العسل واللوز',
            'quantity' => 4,
            'unit_price_fils' => 5000,
            'line_total_fils' => 20000,
        ]);

        $cancelled = $this->makeOrder($store, $customer, [
            'status' => OrderStatus::Cancelled,
            'total_fils' => 5000,
        ]);
        $this->setCreatedAt($cancelled, now()->subDay());

        $active = $this->makeOrder($store, $customer, [
            'status' => OrderStatus::New,
            'total_fils' => 7000,
        ]);
        $this->setCreatedAt($active, now()->subDay());

        $outOfRange = $this->makeOrder($store, $customer, ['total_fils' => 90000]);
        $this->setCreatedAt($outOfRange, now()->subDays(70));

        [$otherStore, $otherCustomer] = $this->makeStoreWithCustomer('analytics-other-summary');
        $otherOrder = $this->makeOrder($otherStore, $otherCustomer, ['total_fils' => 60000]);
        $this->setCreatedAt($otherOrder, now()->subDay());

        $summary = app(AnalyticsService::class)->periodSummary(
            $store,
            today()->subDays(29)->startOfDay(),
            today()->endOfDay(),
        );

        $this->assertSame(20000, $summary['revenue']);
        $this->assertSame(1, $summary['delivered']);
        $this->assertSame(1, $summary['cancelled']);
        $this->assertSame(2, $summary['orders']);
        $this->assertSame(20000, $summary['avg_order']);
        $this->assertSame(50, $summary['completion_rate']);
        $this->assertSame(4, $summary['items_sold']);
        $this->assertSame(1, $summary['unique_customers']);
        $this->assertSame(1, $summary['new_customers']);
    }

    public function test_top_products_can_be_ranked_by_revenue_or_quantity(): void
    {
        [$store, $customer] = $this->makeStoreWithCustomer('analytics-products');

        $cake = $this->makeOrder($store, $customer, ['total_fils' => 30000]);
        $cake->items()->create([
            'product_name' => 'كيكة العسل',
            'quantity' => 2,
            'unit_price_fils' => 15000,
            'line_total_fils' => 30000,
        ]);

        $cookies = $this->makeOrder($store, $customer, ['total_fils' => 18000]);
        $cookies->items()->create([
            'product_name' => 'كوكيز الشوكولاتة',
            'quantity' => 6,
            'unit_price_fils' => 3000,
            'line_total_fils' => 18000,
        ]);

        $analytics = app(AnalyticsService::class);
        $from = today()->subDays(29)->startOfDay();
        $to = today()->endOfDay();

        $byRevenue = $analytics->topProductsBetween($store, $from, $to);
        $this->assertSame('كيكة العسل', $byRevenue[0]['product_name']);
        $this->assertSame(30000, (int) $byRevenue[0]['revenue']);

        $byQuantity = $analytics->topProductsBetween($store, $from, $to, 5, 'sold');
        $this->assertSame('كوكيز الشوكولاتة', $byQuantity[0]['product_name']);
        $this->assertSame(6, (int) $byQuantity[0]['sold']);
    }

    public function test_order_and_payment_breakdowns_exclude_cancelled_orders(): void
    {
        [$store, $customer] = $this->makeStoreWithCustomer('analytics-breakdowns');

        $this->makeOrder($store, $customer, [
            'fulfillment_type' => FulfillmentType::Delivery,
            'payment_method' => PaymentMethod::Cash,
        ]);
        $this->makeOrder($store, $customer, [
            'fulfillment_type' => FulfillmentType::Pickup,
            'payment_method' => PaymentMethod::Cliq,
        ]);
        $this->makeOrder($store, $customer, [
            'status' => OrderStatus::Cancelled,
            'fulfillment_type' => FulfillmentType::Pickup,
            'payment_method' => PaymentMethod::BankTransfer,
        ]);

        $analytics = app(AnalyticsService::class);
        $from = today()->subDays(29)->startOfDay();
        $to = today()->endOfDay();

        $statuses = $analytics->statusBreakdownBetween($store, $from, $to);
        $this->assertSame(2, $statuses['delivered']);
        $this->assertSame(1, $statuses['cancelled']);

        $fulfillment = $analytics->fulfillmentSplitBetween($store, $from, $to);
        $this->assertSame(1, $fulfillment['delivery']);
        $this->assertSame(1, $fulfillment['pickup']);

        $payments = $analytics->paymentSplitBetween($store, $from, $to);
        $this->assertSame(1, $payments['cash']);
        $this->assertSame(1, $payments['cliq']);
        $this->assertArrayNotHasKey('bank_transfer', $payments);
    }

    public function test_analytics_do_not_include_another_stores_orders(): void
    {
        [$otherStore, $otherCustomer] = $this->makeStoreWithCustomer('analytics-isolation');
        $otherOrder = $this->makeOrder($otherStore, $otherCustomer, ['total_fils' => 77000]);
        $otherOrder->items()->create([
            'product_name' => 'منتج المتجر الآخر',
            'quantity' => 2,
            'unit_price_fils' => 38500,
            'line_total_fils' => 77000,
        ]);

        $this->actingAs($this->owner)
            ->get(route('analytics.index'))
            ->assertOk()
            ->assertSee('حركة الإيرادات')
            ->assertDontSee('منتج المتجر الآخر');
    }

    /**
     * @return array{0: Store, 1: Customer}
     */
    protected function makeStoreWithCustomer(string $slug): array
    {
        $owner = User::create([
            'name' => 'مالك تجريبي',
            'email' => Str::random(10).'@example.com',
            'password' => 'secret1234',
        ]);

        $store = Store::create([
            'owner_id' => $owner->id,
            'name' => 'متجر '.$slug,
            'slug' => $slug,
            'phone' => '0790000000',
        ]);

        $customer = Customer::create([
            'store_id' => $store->id,
            'name' => 'عميل تجريبي',
            'phone' => '079'.random_int(1000000, 9999999),
        ]);

        return [$store, $customer];
    }

    protected function makeOrder(Store $store, Customer $customer, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'number' => 'AN-'.Str::random(10),
            'status' => OrderStatus::Delivered,
            'fulfillment_type' => FulfillmentType::Delivery,
            'scheduled_at' => now(),
            'subtotal_fils' => 10000,
            'total_fils' => 10000,
            'payment_method' => PaymentMethod::Cash,
        ], $attributes));
    }

    protected function setCreatedAt(Order $order, \DateTimeInterface $createdAt): void
    {
        $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
    }
}
