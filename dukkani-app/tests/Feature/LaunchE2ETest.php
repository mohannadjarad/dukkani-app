<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\PlanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * Walks the exact journey a real Palestinian home business takes on the Free
 * plan: create the store, fill the catalogue, take a bank-transfer order, run
 * it to delivered, hit the monthly ceiling, then confirm an upgrade lets the
 * next order through.
 */
class LaunchE2ETest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        // The journey places more than ten orders in one client; throttling is
        // covered by CheckoutThrottleTest instead.
        $this->withoutMiddleware(ThrottleRequests::class);

        $this->artisan('dukkani:store:create', [
            '--store-name' => 'حلويات جنين',
            '--slug' => 'jinin-sweets',
            '--owner-name' => 'سارة أحمد',
            '--email' => 'sara@example.com',
            '--password' => 'secret-pass',
            '--phone' => '0599123456',
            '--whatsapp' => '970599123456',
            '--city' => 'جنين',
            '--country-code' => '970',
            '--no-trial' => true,
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->store = Store::where('slug', 'jinin-sweets')->firstOrFail();
        $this->owner = $this->store->owner;

        $this->store->update([
            'bank_name' => 'بنك فلسطين',
            'bank_account_name' => 'سارة أحمد',
            'bank_iban' => 'PS92PALS000000000400123456702',
        ]);
    }

    public function test_a_palestinian_store_offers_cash_and_bank_transfer_in_shekel(): void
    {
        $this->makeProducts(1);
        $this->addToCart();

        $this->assertSame(['cash', 'bank_transfer'], $this->store->allowedPaymentMethods());

        $this->get(route('storefront.checkout', $this->store))
            ->assertOk()
            ->assertSee('₪')
            ->assertDontSee('د.أ')
            ->assertDontSee('CliQ')
            ->assertSee('حوالة بنكية');
    }

    public function test_the_free_plan_journey_from_limit_to_upgrade(): void
    {
        $plans = app(PlanService::class);

        // 1. Fill the Free catalogue: twelve products, the thirteenth is blocked.
        $this->makeProducts(12);
        $this->assertSame(12, $this->store->products()->count());
        $this->assertFalse($plans->canAddProduct($this->store));

        // 2. A bank-transfer order charges the full amount, with no deposit.
        $this->placeOrder('bank_transfer');

        $order = Order::latest('id')->firstOrFail();
        $this->assertSame(10000, $order->subtotal_fils);
        $this->assertSame(10000, $order->balance_due_fils);

        $this->get(route('track.show', $order->public_token))
            ->assertOk()
            ->assertSee('حوّلي المبلغ كاملاً إلى الحساب البنكي')
            ->assertSee('بنك فلسطين')
            ->assertSee('سارة أحمد')
            ->assertSee('PS92PALS000000000400123456702')
            ->assertSee('أرسلي صورة التحويل')
            ->assertDontSee('عربون');

        // 3. The owner runs the order through the workflow to delivered.
        foreach ([OrderStatus::Confirmed, OrderStatus::InProduction, OrderStatus::Ready, OrderStatus::Delivered] as $to) {
            $this->actingAs($this->owner)
                ->patch(route('orders.status', $order), ['status' => $to->value])
                ->assertRedirect();

            $this->assertSame($to, $order->refresh()->status);
        }

        // 4. Fill the rest of the month up to the Free ceiling of fifteen orders.
        $filled = 0;
        while ($plans->monthlyOrders($this->store) < 15 && $filled < 40) {
            $this->placeOrder('cash');
            $filled++;
        }

        $this->assertSame(15, $plans->monthlyOrders($this->store));
        $this->assertFalse($plans->canAcceptOrder($this->store));

        // 5. The sixteenth order is blocked and the customer sees the friendly page.
        $this->addToCart();
        $this->post(route('storefront.checkout.store', $this->store), $this->payload())
            ->assertRedirect(route('storefront.checkout.limit', $this->store));

        $this->assertSame(15, Order::where('store_id', $this->store->id)->count());

        // 6. An upgrade lifts the ceiling and the next order goes through.
        $plans->activate($this->store, 'starter', 1);
        $this->store->refresh();

        $this->assertTrue($plans->isUnlimited($this->store));
        $this->assertTrue($plans->canAcceptOrder($this->store));

        $this->placeOrder('cash');

        $this->assertSame(16, Order::where('store_id', $this->store->id)->count());
    }

    protected function makeProducts(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Product::create([
                'store_id' => $this->store->id,
                'name' => 'منتج '.$i,
                'slug' => 'product-'.$i,
                'price_fils' => 10000,
                'is_active' => true,
            ]);
        }
    }

    protected function addToCart(int $quantity = 1): void
    {
        $product = $this->store->products()->firstOrFail();

        $this->post(route('storefront.cart.add', $this->store), [
            'product_id' => $product->id,
            'quantity' => $quantity,
        ])->assertRedirect();
    }

    protected function placeOrder(string $payment): void
    {
        $this->addToCart();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'payment_method' => $payment,
        ]))->assertRedirect();
    }

    /** @return array<string, mixed> */
    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'نور الخالدي',
            'phone' => '0599112233',
            'fulfillment_type' => 'pickup',
            'scheduled_date' => $this->openDate()->toDateString(),
            'scheduled_time' => '12:00',
            'payment_method' => 'cash',
            'customer_notes' => 'طلب فحص الإطلاق.',
            'consent' => '1',
        ], $overrides);
    }

    protected function openDate(): Carbon
    {
        $date = now()->startOfDay()->addDays(3);

        for ($offset = 0; $offset < 45; $offset++) {
            $hours = $this->store->hours()->where('day_of_week', $date->dayOfWeek)->first();

            if ($hours && ! $hours->is_closed && $date->copy()->startOfDay()->gte(now()->startOfDay()->addDays(2))) {
                return $date->copy();
            }

            $date->addDay();
        }

        $this->fail('No open date found for the new store.');
    }
}
