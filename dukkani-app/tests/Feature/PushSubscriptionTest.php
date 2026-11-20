<?php

namespace Tests\Feature;

use App\Models\PushSubscription;
use App\Models\Store;
use App\Models\User;
use App\Services\WebPushService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
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

    public function test_guests_cannot_manage_push_subscriptions(): void
    {
        $this->postJson(route('push.subscribe'), [])->assertUnauthorized();
        $this->postJson(route('push.test'), [])->assertUnauthorized();
    }

    public function test_an_owner_can_register_a_device_once(): void
    {
        $payload = [
            'endpoint' => 'https://push.example.com/device-a',
            'keys' => ['p256dh' => 'public-key-a', 'auth' => 'auth-token-a'],
        ];

        $this->actingAs($this->owner)
            ->postJson(route('push.subscribe'), $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $this->owner->id,
            'endpoint' => $payload['endpoint'],
            'public_key' => 'public-key-a',
            'content_encoding' => 'aes128gcm',
        ]);

        // Refreshing the page resubscribes with the same endpoint, which must
        // update the keys instead of creating a second row.
        $this->actingAs($this->owner)
            ->postJson(route('push.subscribe'), [
                'endpoint' => $payload['endpoint'],
                'keys' => ['p256dh' => 'public-key-b', 'auth' => 'auth-token-b'],
            ])
            ->assertOk();

        $this->assertSame(1, PushSubscription::where('user_id', $this->owner->id)->count());
        $this->assertDatabaseHas('push_subscriptions', [
            'endpoint' => $payload['endpoint'],
            'public_key' => 'public-key-b',
        ]);
    }

    public function test_a_subscription_needs_valid_keys(): void
    {
        $this->actingAs($this->owner)
            ->postJson(route('push.subscribe'), ['endpoint' => 'not-a-url'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['endpoint', 'keys.p256dh', 'keys.auth']);
    }

    public function test_unsubscribing_removes_only_that_device(): void
    {
        PushSubscription::create([
            'user_id' => $this->owner->id,
            'endpoint' => 'https://push.example.com/device-a',
            'public_key' => 'key-a',
            'auth_token' => 'auth-a',
        ]);

        PushSubscription::create([
            'user_id' => $this->owner->id,
            'endpoint' => 'https://push.example.com/device-b',
            'public_key' => 'key-b',
            'auth_token' => 'auth-b',
        ]);

        $this->actingAs($this->owner)
            ->postJson(route('push.unsubscribe'), ['endpoint' => 'https://push.example.com/device-a'])
            ->assertOk();

        $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => 'https://push.example.com/device-a']);
        $this->assertDatabaseHas('push_subscriptions', ['endpoint' => 'https://push.example.com/device-b']);
    }

    public function test_test_notifications_are_a_no_op_without_keys(): void
    {
        config(['webpush.public_key' => null, 'webpush.private_key' => null]);

        $this->assertFalse(app(WebPushService::class)->configured());

        $this->actingAs($this->owner)
            ->postJson(route('push.test'))
            ->assertOk()
            ->assertJson(['ok' => false, 'sent' => 0]);
    }

    public function test_the_pwa_icons_exist_where_the_manifest_points(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);

        $this->assertIsArray($manifest);
        $this->assertNotEmpty($manifest['icons'] ?? []);

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }

        // Apache ships an /icons/ alias that shadows public/icons, so the app
        // must keep its own icons under a path it controls.
        $this->assertStringNotContainsString("'/icons/", file_get_contents(public_path('sw.js')));
    }

    public function test_the_production_image_installs_bcmath_for_web_push(): void
    {
        // Without GMP or BCMath, web-push raises a notice and Laravel turns it
        // into an exception, so every notification fails on a correct setup.
        $dockerfile = file_get_contents(base_path('Dockerfile'));

        $this->assertMatchesRegularExpression(
            '/docker-php-ext-install[^\n]*\bbcmath\b/',
            $dockerfile,
        );
    }

    public function test_the_dashboard_exposes_push_settings_only_when_configured(): void
    {
        config(['webpush.public_key' => null]);

        $this->actingAs($this->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('name="vapid-public-key"', false)
            ->assertDontSee('id="push-prompt"', false);

        config(['webpush.public_key' => 'test-vapid-public-key']);

        $this->actingAs($this->owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('name="vapid-public-key"', false)
            ->assertSee('test-vapid-public-key')
            ->assertSee('id="push-prompt"', false)
            ->assertSee('id="push-banner"', false);
    }
}
