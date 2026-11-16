<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StoreHour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateStoreCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_owner_store_and_opening_hours(): void
    {
        $this->artisan('dukkani:store:create', [
            '--store-name' => 'حلويات جنين',
            '--slug' => 'jinin-sweets',
            '--owner-name' => 'سارة أحمد',
            '--email' => 'Sara@Example.com',
            '--password' => 'secret-pass',
            '--phone' => '0599123456',
            '--whatsapp' => '970599123456',
            '--city' => 'جنين',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $user = User::where('email', 'sara@example.com')->firstOrFail();
        $store = Store::where('slug', 'jinin-sweets')->firstOrFail();

        $this->assertSame($user->id, $store->owner_id);
        $this->assertSame('حلويات جنين', $store->name);
        $this->assertSame('جنين', $store->city);
        $this->assertSame('active', $store->status);
        $this->assertNotNull($store->onboarding_completed_at);
        $this->assertSame(7, StoreHour::where('store_id', $store->id)->count());
        $this->assertSame(1, StoreHour::where('store_id', $store->id)->where('is_closed', true)->count());
    }

    public function test_it_rejects_a_duplicate_slug(): void
    {
        $owner = User::factory()->create();

        Store::create([
            'owner_id' => $owner->id,
            'name' => 'متجر موجود',
            'slug' => 'taken-slug',
            'phone' => '0599000000',
        ]);

        $this->artisan('dukkani:store:create', [
            '--store-name' => 'متجر ثانٍ',
            '--slug' => 'taken-slug',
            '--owner-name' => 'مالك',
            '--email' => 'owner@example.com',
            '--password' => 'secret-pass',
            '--phone' => '0599123456',
            '--no-interaction' => true,
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'owner@example.com']);
        $this->assertSame(1, Store::count());
    }

    public function test_it_derives_currency_and_timezone_from_the_country(): void
    {
        $this->artisan('dukkani:store:create', [
            '--store-name' => 'حلويات جنين',
            '--slug' => 'jinin-sweets',
            '--owner-name' => 'سارة أحمد',
            '--email' => 'sara@example.com',
            '--password' => 'secret-pass',
            '--phone' => '0599123456',
            '--city' => 'جنين',
            '--country-code' => '970',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $store = Store::where('slug', 'jinin-sweets')->firstOrFail();

        $this->assertSame('ILS', $store->currency);
        $this->assertSame('Asia/Hebron', $store->timezone);
        $this->assertSame('970', $store->country_code);
    }
}
