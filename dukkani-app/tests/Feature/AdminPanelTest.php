<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $owner;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'مدير المنصة',
            'email' => 'admin@dukkani.test',
            'password' => 'secret1234',
            'is_admin' => true,
        ]);

        $this->owner = User::create([
            'name' => 'مالكة المتجر',
            'email' => 'owner@dukkani.test',
            'password' => 'secret1234',
        ]);

        $this->store = Store::create([
            'owner_id' => $this->owner->id,
            'name' => 'حلويات جنين',
            'slug' => 'jinin-sweets',
            'phone' => '0599123456',
            'whatsapp' => '970599123456',
            'city' => 'جنين',
            'currency' => 'ILS',
            'country_code' => '970',
            'timezone' => 'Asia/Hebron',
            'status' => 'active',
            'plan' => 'free',
        ]);
    }

    public function test_non_admins_cannot_open_the_admin_panel(): void
    {
        $this->actingAs($this->owner)
            ->get(route('admin.stores.index'))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_from_the_admin_panel(): void
    {
        $this->get(route('admin.stores.index'))->assertRedirect('/login');
    }

    public function test_the_admin_sees_the_store_list_with_owner_and_plan(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.stores.index'))
            ->assertOk()
            ->assertSee('حلويات جنين')
            ->assertSee('مالكة المتجر')
            ->assertSee('owner@dukkani.test')
            ->assertSee('المجانية');
    }

    public function test_the_admin_can_activate_starter_for_three_months(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.plan', $this->store), [
                'plan' => 'starter',
                'months' => 3,
            ])
            ->assertRedirect();

        $this->store->refresh();

        $this->assertSame('starter', $this->store->plan);
        $this->assertFalse($this->store->is_trial);
        $this->assertNotNull($this->store->plan_expires_at);
        $this->assertTrue($this->store->plan_expires_at->between(now()->addMonths(3)->subDay(), now()->addMonths(3)->addDay()));
        $this->assertNull(app(\App\Services\PlanService::class)->orderLimit($this->store));
    }

    public function test_the_admin_can_grant_an_order_override(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.override', $this->store), ['order_limit_override' => 5])
            ->assertRedirect();

        $this->assertSame(5, $this->store->refresh()->order_limit_override);
        $this->assertSame(20, app(\App\Services\PlanService::class)->orderLimit($this->store));
    }

    public function test_the_admin_can_suspend_and_reopen_a_store(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.suspend', $this->store))
            ->assertRedirect();

        $this->assertSame('suspended', $this->store->refresh()->status);
        $this->get(route('storefront.home', $this->store))->assertNotFound();

        $this->actingAs($this->admin)
            ->post(route('admin.stores.activate', $this->store))
            ->assertRedirect();

        $this->assertSame('active', $this->store->refresh()->status);
        $this->get(route('storefront.home', $this->store))->assertOk();
    }

    public function test_login_records_last_login_and_sends_admins_to_the_panel(): void
    {
        $this->post(route('login.store'), [
            'email' => 'admin@dukkani.test',
            'password' => 'secret1234',
        ])->assertRedirect(route('admin.stores.index'));

        $this->assertNotNull($this->admin->refresh()->last_login_at);
    }

    public function test_a_store_owner_logs_in_to_the_dashboard(): void
    {
        $this->post(route('login.store'), [
            'email' => 'owner@dukkani.test',
            'password' => 'secret1234',
        ])->assertRedirect(route('dashboard'));

        $this->assertNotNull($this->owner->refresh()->last_login_at);
    }

    public function test_the_grant_admin_command_promotes_and_revokes(): void
    {
        $email = 'new-'.Str::lower(Str::random(6)).'@dukkani.test';

        User::create(['name' => 'مستخدم', 'email' => $email, 'password' => 'secret1234']);

        $this->artisan('dukkani:admin:grant', ['email' => $email])->assertSuccessful();
        $this->assertTrue(User::where('email', $email)->firstOrFail()->is_admin);

        $this->artisan('dukkani:admin:grant', ['email' => $email, '--revoke' => true])->assertSuccessful();
        $this->assertFalse(User::where('email', $email)->firstOrFail()->is_admin);
    }
}