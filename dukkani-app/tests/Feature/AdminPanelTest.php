<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\StoreHour;
use App\Models\User;
use App\Services\PlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
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

    public function test_the_admin_can_create_a_store_with_an_owner_login(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.store'), [
                'store_name' => 'مخبز الياسمين',
                'slug' => 'Yasmin-Bakery',
                'owner_name' => 'ليلى حسن',
                'email' => 'Layla@Example.com',
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
                'phone' => '0599888777',
                'whatsapp' => '970599888777',
                'city' => 'نابلس',
                'country_code' => '970',
                'start_trial' => '1',
            ])
            ->assertRedirect(route('admin.stores.index'));

        $user = User::where('email', 'layla@example.com')->firstOrFail();
        $store = Store::where('slug', 'yasmin-bakery')->firstOrFail();

        $this->assertTrue(Hash::check('secret-pass', $user->password));
        $this->assertSame($user->id, $store->owner_id);
        $this->assertSame('مخبز الياسمين', $store->name);
        $this->assertSame('active', $store->status);
        $this->assertSame('ILS', $store->currency);
        $this->assertSame('Asia/Hebron', $store->timezone);
        $this->assertNotNull($store->onboarding_completed_at);
        $this->assertSame(7, StoreHour::where('store_id', $store->id)->count());
        $this->assertSame('starter', $store->plan);
        $this->assertTrue($store->is_trial);
        $this->assertTrue($store->plan_expires_at->between(now()->addDays(2), now()->addDays(4)));
    }

    public function test_the_admin_can_create_a_store_without_a_trial(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.store'), [
                'store_name' => 'متجر بلا تجربة',
                'slug' => 'no-trial-store',
                'owner_name' => 'مالك جديد',
                'email' => 'new-owner@dukkani.test',
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
                'phone' => '0790000000',
                'city' => 'عمّان',
                'country_code' => '962',
            ])
            ->assertRedirect(route('admin.stores.index'));

        $store = Store::where('slug', 'no-trial-store')->firstOrFail();

        $this->assertSame('free', $store->plan);
        $this->assertFalse($store->is_trial);
        $this->assertNull($store->plan_expires_at);
        $this->assertSame(15, app(PlanService::class)->orderLimit($store));
    }

    public function test_store_creation_rejects_duplicate_email_and_slug(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.store'), [
                'store_name' => 'متجر مكرر',
                'slug' => $this->store->slug,
                'owner_name' => 'مالك مكرر',
                'email' => $this->owner->email,
                'password' => 'secret-pass',
                'password_confirmation' => 'secret-pass',
                'phone' => '0790000000',
                'city' => 'عمّان',
                'country_code' => '962',
            ])
            ->assertSessionHasErrors(['slug', 'email']);

        $this->assertSame(1, Store::count());
        $this->assertSame(2, User::count());
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
        $this->assertNotNull($this->store->first_paid_at);
        $this->assertNotNull($this->store->plan_expires_at);
        // Three paid months plus the one-off first-month bonus days.
        $this->assertTrue($this->store->plan_expires_at->between(
            now()->addMonths(3)->addDays(6),
            now()->addMonths(3)->addDays(8),
        ));
        $this->assertNull(app(PlanService::class)->orderLimit($this->store));
    }

    public function test_the_admin_cannot_assign_pro_because_it_is_not_for_sale(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.plan', $this->store), [
                'plan' => 'pro',
                'months' => 1,
            ])
            ->assertSessionHasErrors('plan');

        $this->assertSame('free', $this->store->refresh()->plan);
    }

    public function test_the_admin_can_set_an_exact_plan_end_date(): void
    {
        $this->store->forceFill([
            'plan' => 'starter',
            'plan_expires_at' => now()->addDays(5),
            'is_trial' => true,
            'order_limit_override' => 7,
        ])->save();

        $endsAt = now()->addDays(10)->toDateString();

        $this->actingAs($this->admin)
            ->post(route('admin.stores.plan', $this->store), [
                'plan' => 'starter',
                'ends_at' => $endsAt,
            ])
            ->assertRedirect();

        $this->store->refresh();

        $this->assertSame('starter', $this->store->plan);
        $this->assertFalse($this->store->is_trial);
        $this->assertNotNull($this->store->first_paid_at);
        $this->assertSame($endsAt, $this->store->plan_expires_at->toDateString());
        $this->assertNull($this->store->order_limit_override);
        $this->assertNull(app(PlanService::class)->orderLimit($this->store));
    }

    public function test_the_admin_can_put_a_store_on_a_timed_trial(): void
    {
        $endsAt = now()->addDays(2)->toDateString();

        $this->actingAs($this->admin)
            ->post(route('admin.stores.plan', $this->store), [
                'plan' => 'starter',
                'ends_at' => $endsAt,
                'is_trial' => '1',
            ])
            ->assertRedirect();

        $this->store->refresh();

        $this->assertSame('starter', $this->store->plan);
        $this->assertTrue($this->store->is_trial);
        $this->assertSame($endsAt, $this->store->plan_expires_at->toDateString());
        $this->assertNull($this->store->first_paid_at);
        $this->assertNull(app(PlanService::class)->orderLimit($this->store));
    }

    public function test_the_admin_cannot_set_a_plan_end_date_in_the_past(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.plan', $this->store), [
                'plan' => 'starter',
                'ends_at' => now()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors('ends_at');

        $this->assertSame('free', $this->store->refresh()->plan);
    }

    public function test_the_admin_can_extend_a_plan_from_its_current_end_date(): void
    {
        $expiry = Carbon::now()->addDays(10)->startOfDay();

        $this->store->forceFill([
            'plan' => 'starter',
            'plan_expires_at' => $expiry,
            'is_trial' => false,
        ])->save();

        $this->actingAs($this->admin)
            ->post(route('admin.stores.extend', $this->store), ['extend_months' => 3])
            ->assertRedirect();

        $this->store->refresh();

        $this->assertSame(
            $expiry->copy()->addMonths(3)->toDateString(),
            $this->store->plan_expires_at->toDateString(),
        );
    }

    public function test_the_admin_cannot_extend_a_free_plan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.extend', $this->store), ['extend_months' => 3])
            ->assertSessionHasErrors('extend_months');

        $this->assertNull($this->store->refresh()->plan_expires_at);
    }

    public function test_the_admin_can_grant_an_order_override(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.stores.override', $this->store), ['order_limit_override' => 5])
            ->assertRedirect();

        $this->assertSame(5, $this->store->refresh()->order_limit_override);
        $this->assertSame(20, app(PlanService::class)->orderLimit($this->store));
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
