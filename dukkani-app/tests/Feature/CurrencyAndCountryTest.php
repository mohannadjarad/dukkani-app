<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Support\Money;
use App\Support\Phone;
use Carbon\Carbon;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyAndCountryTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected string $originalTimezone;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalTimezone = date_default_timezone_get();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
    }

    protected function tearDown(): void
    {
        Money::useCurrency(null);
        date_default_timezone_set($this->originalTimezone);

        parent::tearDown();
    }

    public function test_ils_uses_the_shekel_symbol_and_two_decimals(): void
    {
        Money::useCurrency('ILS');

        $this->assertSame('₪', Money::symbol());
        $this->assertSame(2, Money::decimals());
        $this->assertSame('100.00 ₪', Money::format(100000));
        // 100 ₪ of products plus 10 ₪ delivery.
        $this->assertSame('110.00 ₪', Money::format(100000 + 10000));
    }

    public function test_jod_uses_the_dinar_symbol_and_three_decimals(): void
    {
        Money::useCurrency('JOD');

        $this->assertSame('د.أ', Money::symbol());
        $this->assertSame(3, Money::decimals());
        $this->assertSame('18.500 د.أ', Money::format(18500));
    }

    public function test_palestinian_storefront_formats_prices_in_shekel(): void
    {
        $this->store->update(['currency' => 'ILS', 'country_code' => '970']);

        $this->get(route('storefront.home', $this->store))
            ->assertOk()
            ->assertSee('₪')
            ->assertDontSee('د.أ');
    }

    public function test_cliq_is_only_offered_in_jordan(): void
    {
        $jordan = new Store(['country_code' => '962']);
        $palestine = new Store(['country_code' => '970']);
        $jordanWithBank = new Store([
            'country_code' => '962',
            'bank_name' => 'البنك العربي',
            'bank_iban' => 'JO94CBJO0010000000000131000302',
        ]);
        $palestineWithBank = new Store([
            'country_code' => '970',
            'bank_name' => 'بنك فلسطين',
            'bank_iban' => 'PS92PALS000000000400123456702',
        ]);

        $this->assertSame(['cash', 'cliq'], $jordan->allowedPaymentMethods());
        $this->assertSame(['cash'], $palestine->allowedPaymentMethods());
        $this->assertSame(['cash', 'cliq', 'bank_transfer'], $jordanWithBank->allowedPaymentMethods());
        $this->assertSame(['cash', 'bank_transfer'], $palestineWithBank->allowedPaymentMethods());
    }

    public function test_bank_transfer_is_hidden_until_a_bank_name_and_an_iban_are_saved(): void
    {
        $store = new Store(['country_code' => '970', 'bank_name' => 'بنك فلسطين']);

        $this->assertFalse($store->hasBankDetails());
        $this->assertSame(['cash'], $store->allowedPaymentMethods());

        $store->bank_iban = 'PS92PALS000000000400123456702';

        $this->assertTrue($store->hasBankDetails());
        $this->assertSame(['cash', 'bank_transfer'], $store->allowedPaymentMethods());
    }

    public function test_checkout_rejects_cliq_for_a_palestinian_store(): void
    {
        $this->store->update(['currency' => 'ILS', 'country_code' => '970']);
        $this->addToCart();

        $before = Order::count();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'payment_method' => 'cliq',
        ]))->assertSessionHasErrors('payment_method');

        $this->assertSame($before, Order::count());
    }

    public function test_the_store_timezone_is_applied_to_the_request(): void
    {
        $this->store->update(['timezone' => 'Asia/Hebron']);

        $this->get(route('storefront.home', $this->store))->assertOk();

        $this->assertSame('Asia/Hebron', date_default_timezone_get());
    }

    public function test_phone_numbers_are_normalized_with_the_store_dial_code(): void
    {
        $this->assertSame('970599112233', Phone::normalize('0599112233', '970'));
        $this->assertSame('962791111001', Phone::normalize('0791111001', '962'));

        $this->store->update(['country_code' => '970', 'phone' => '0599112233', 'whatsapp' => null]);

        $this->assertSame('https://wa.me/970599112233', $this->store->whatsappLink());
    }

    public function test_customer_phone_is_stored_in_international_form(): void
    {
        $this->store->update(['country_code' => '970', 'currency' => 'ILS']);
        $this->addToCart();

        $this->post(route('storefront.checkout.store', $this->store), $this->payload([
            'phone' => '0599112233',
            'payment_method' => 'cash',
        ]));

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame('970599112233', $order->customer->phone);
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
            'phone' => '0599112233',
            'fulfillment_type' => 'delivery',
            'delivery_address' => 'جنين، شارع حيفا، بناية ١٤',
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
