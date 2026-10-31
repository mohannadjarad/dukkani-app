<?php

namespace Tests\Feature;

use App\Enums\DepositStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Carbon\Carbon;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
    }

    public function test_checkout_requires_a_non_empty_cart(): void
    {
        $this->get(route('storefront.checkout', $this->store))
            ->assertRedirect(route('storefront.menu', $this->store));
    }

    public function test_checkout_page_renders_with_the_cart_summary(): void
    {
        $this->addToCart();

        $this->get(route('storefront.checkout', $this->store))
            ->assertOk()
            ->assertSee('إتمام الطلب')
            ->assertSee('بيانات التواصل')
            ->assertSee('طريقة الاستلام')
            ->assertSee('طريقة الدفع')
            ->assertSee('ملخص الطلب')
            ->assertSee('كيكة العسل واللوز');
    }

    public function test_cash_delivery_order_is_created_and_updates_the_dashboard(): void
    {
        $this->addToCart();

        $response = $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'عمّان، عبدون، شارع الأمير هاشم، بناية ١٤',
            'payment_method' => 'cash',
        ]));

        $order = Order::latest('id')->firstOrFail();

        $response->assertRedirect(route('track.show', $order->public_token));

        $this->assertSame(18500, $order->subtotal_fils);
        $this->assertSame(2000, $order->delivery_fee_fils);
        $this->assertSame(20500, $order->total_fils);
        $this->assertSame(0, $order->deposit_fils);
        $this->assertSame(20500, $order->balance_due_fils);
        $this->assertSame(1, $order->items()->count());
        $this->assertNull(session('cart.'.$this->store->id));

        $this->get(route('track.show', $order->public_token))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('وصلنا طلبك، وبانتظار التأكيد')
            ->assertSee('كيكة العسل واللوز');

        $this->actingAs($this->store->owner)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee($order->number);
    }

    public function test_pickup_order_has_no_delivery_fee(): void
    {
        $this->addToCart();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'fulfillment_type' => 'pickup',
            'delivery_address' => null,
            'payment_method' => 'cash',
        ]));

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame(0, $order->delivery_fee_fils);
        $this->assertSame(18500, $order->total_fils);
        $this->assertNull($order->delivery_address);
    }

    public function test_cliq_order_requires_a_thirty_percent_deposit(): void
    {
        $this->addToCart();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'payment_method' => 'cliq',
        ]));

        $order = Order::latest('id')->firstOrFail();

        $expectedDeposit = (int) round(20500 * 0.3);

        $this->assertSame($expectedDeposit, $order->deposit_fils);
        $this->assertSame(DepositStatus::Pending, $order->deposit_status);
        $this->assertSame(20500 - $expectedDeposit, $order->balance_due_fils);
    }

    public function test_schedule_outside_opening_hours_is_rejected(): void
    {
        $this->addToCart();

        $before = Order::count();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'scheduled_time' => '23:00',
        ]))->assertSessionHasErrors('scheduled_time');

        $this->assertSame($before, Order::count());
    }

    public function test_blocked_dates_are_rejected(): void
    {
        $this->addToCart();

        $blocked = $this->store->blockedDates()->firstOrFail();
        $before = Order::count();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'scheduled_date' => $blocked->date->toDateString(),
        ]))->assertSessionHasErrors('scheduled_date');

        $this->assertSame($before, Order::count());
    }

    public function test_lead_time_is_enforced(): void
    {
        $this->addToCart();

        $before = Order::count();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'scheduled_date' => now()->addDay()->toDateString(),
        ]))->assertSessionHasErrors('scheduled_date');

        $this->assertSame($before, Order::count());
    }

    public function test_consent_and_delivery_address_are_required(): void
    {
        $this->addToCart();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'delivery_address' => null,
            'consent' => null,
        ]))->assertSessionHasErrors(['delivery_address', 'consent']);
    }

    public function test_unknown_tracking_token_returns_not_found(): void
    {
        $this->get(route('track.show', (string) Str::uuid()))->assertNotFound();
    }

    private function addToCart(int $quantity = 1): void
    {
        $product = Product::where('slug', 'honey-almond-cake')->firstOrFail();
        $option = $product->options()->where('is_required', true)->firstOrFail();
        $value = $option->values()->firstOrFail();

        $this->post(route('storefront.cart.add', $this->store), [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'options' => [$option->id => $value->id],
        ])->assertRedirect();
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'نور الخالدي',
            'phone' => '0791111001',
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'عمّان، عبدون، شارع الأمير هاشم، بناية ١٤',
            'scheduled_date' => $this->openDate()->toDateString(),
            'scheduled_time' => '12:00',
            'payment_method' => 'cash',
            'customer_notes' => 'مناسبة عائلية صغيرة.',
            'consent' => '1',
        ], $overrides);
    }

    private function openDate(): Carbon
    {
        $date = now()->startOfDay()->addDays(5);

        for ($offset = 0; $offset < 45; $offset++) {
            $hours = $this->store->hours()->where('day_of_week', $date->dayOfWeek)->first();

            if ($hours
                && ! $hours->is_closed
                && ! $this->store->blockedDates()->whereDate('date', $date->toDateString())->exists()) {
                return $date->copy();
            }

            $date->addDay();
        }

        $this->fail('No open date found for the demo store.');
    }
}