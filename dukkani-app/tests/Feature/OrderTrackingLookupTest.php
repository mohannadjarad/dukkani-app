<?php

namespace Tests\Feature;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Store;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingLookupTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
    }

    public function test_the_tracking_lookup_page_is_available_to_customers(): void
    {
        $this->get(route('storefront.track.lookup', $this->store))
            ->assertOk()
            ->assertSee('متابعة طلبي')
            ->assertSee('رقم الهاتف');
    }

    public function test_a_phone_with_several_orders_lists_them_all(): void
    {
        $customer = Customer::where('store_id', $this->store->id)
            ->has('orders', '>=', 2)
            ->firstOrFail();

        $numbers = $customer->orders()->pluck('number')->all();

        $response = $this->followingRedirects()
            ->post(route('storefront.track.find', $this->store), ['phone' => $customer->phone])
            ->assertOk()
            ->assertSee('طلباتك بهذا الرقم');

        foreach ($numbers as $number) {
            $response->assertSee($number);
        }

        // The matches are one-shot, so a refresh goes back to the form.
        $this->get(route('storefront.track.lookup', $this->store))
            ->assertOk()
            ->assertDontSee('طلباتك بهذا الرقم');
    }

    public function test_the_country_code_is_accepted_for_the_same_phone(): void
    {
        $customer = Customer::where('store_id', $this->store->id)
            ->has('orders', '>=', 2)
            ->firstOrFail();

        // Seeded numbers are local (079...), while customers often type +962 79...
        $international = '+962'.substr($customer->phone, 1);

        $this->post(route('storefront.track.find', $this->store), ['phone' => $international])
            ->assertRedirect(route('storefront.track.lookup', $this->store));

        $this->get(route('storefront.track.lookup', $this->store))
            ->assertOk()
            ->assertSee($customer->orders()->first()->number);
    }

    public function test_an_unknown_phone_shows_a_friendly_error(): void
    {
        $this->from(route('storefront.track.lookup', $this->store))
            ->post(route('storefront.track.find', $this->store), ['phone' => '0799999999'])
            ->assertRedirect(route('storefront.track.lookup', $this->store))
            ->assertSessionHasErrors('phone');
    }

    public function test_a_customer_with_one_order_goes_straight_to_tracking(): void
    {
        $customer = Customer::create([
            'store_id' => $this->store->id,
            'name' => 'عميل جديد',
            'phone' => '0790000001',
        ]);

        $order = Order::create([
            'store_id' => $this->store->id,
            'customer_id' => $customer->id,
            'number' => 'AB-TEST-001',
            'status' => OrderStatus::New,
            'fulfillment_type' => FulfillmentType::Pickup,
            'scheduled_at' => now()->addDay(),
            'subtotal_fils' => 1500,
            'total_fils' => 1500,
            'payment_method' => PaymentMethod::Cash,
            'balance_due_fils' => 1500,
        ]);

        $this->post(route('storefront.track.find', $this->store), ['phone' => '079 000 0001'])
            ->assertRedirect(route('track.show', $order->public_token));
    }
}