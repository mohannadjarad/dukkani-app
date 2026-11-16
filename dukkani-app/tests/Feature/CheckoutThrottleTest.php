<?php

namespace Tests\Feature;

use App\Models\Store;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
    }

    public function test_the_checkout_post_is_capped_at_ten_attempts_per_minute(): void
    {
        $url = route('storefront.checkout.store', $this->store);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->post($url, [])->assertStatus(302);
        }

        $this->post($url, [])->assertStatus(429);
    }

    public function test_the_checkout_page_itself_is_not_throttled(): void
    {
        $url = route('storefront.checkout.store', $this->store);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->post($url, []);
        }

        // The POST is now rate limited, but the page a customer can still open.
        $this->post($url, [])->assertStatus(429);
        $this->get(route('storefront.menu', $this->store))->assertOk();
    }
}
