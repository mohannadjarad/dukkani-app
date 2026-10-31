<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\Store;
use App\Models\StoreHour;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
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

    public function test_guests_are_redirected_from_settings(): void
    {
        $this->get('/dashboard/settings')->assertRedirect('/login');
    }

    public function test_the_settings_page_renders_all_sections(): void
    {
        $this->actingAs($this->owner)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('الإعدادات')
            ->assertSee('معلومات المتجر')
            ->assertSee('التوصيل والدفع')
            ->assertSee('ساعات العمل')
            ->assertSee('التواريخ المحظورة');
    }

    public function test_the_store_profile_can_be_updated(): void
    {
        $this->actingAs($this->owner)
            ->patch(route('settings.profile'), [
                'name' => 'أمل بيكنز الجديد',
                'slug' => 'amal-bakes-new',
                'tagline' => 'حلويات من القلب',
                'phone' => '079 111 2222',
                'city' => 'إربد',
                'address' => 'شارع الجامعة',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'profile']));

        $this->store->refresh();

        $this->assertSame('أمل بيكنز الجديد', $this->store->name);
        $this->assertSame('amal-bakes-new', $this->store->slug);
        $this->assertSame('إربد', $this->store->city);
    }

    public function test_the_store_slug_must_be_unique(): void
    {
        $other = Store::create([
            'owner_id' => User::create(['name' => 'مالك', 'email' => 'x@example.com', 'password' => 'secret1234'])->id,
            'name' => 'متجر آخر',
            'slug' => 'taken-slug',
            'phone' => '0790000009',
        ]);

        $this->actingAs($this->owner)
            ->from(route('settings.index'))
            ->patch(route('settings.profile'), [
                'name' => $this->store->name,
                'slug' => $other->slug,
                'phone' => $this->store->phone,
                'city' => $this->store->city,
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_the_delivery_settings_can_be_updated(): void
    {
        $this->actingAs($this->owner)
            ->patch(route('settings.store'), [
                'pickup_enabled' => '1',
                'delivery_enabled' => '1',
                'delivery_fee' => '3.500',
                'deposit_percent' => '40',
                'lead_time_hours' => '24',
                'deposit_instructions' => 'حوّلي عبر CliQ.',
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'delivery']));

        $this->store->refresh();

        $this->assertSame(3500, $this->store->delivery_fee_fils);
        $this->assertSame(40, $this->store->deposit_percent);
        $this->assertSame(24, $this->store->lead_time_hours);
    }

    public function test_at_least_one_fulfillment_method_is_required(): void
    {
        $this->actingAs($this->owner)
            ->from(route('settings.index'))
            ->patch(route('settings.store'), [
                'pickup_enabled' => '0',
                'delivery_enabled' => '0',
                'delivery_fee' => '2',
                'deposit_percent' => '30',
                'lead_time_hours' => '48',
            ])
            ->assertSessionHasErrors('delivery_enabled');
    }

    public function test_working_hours_can_be_updated(): void
    {
        $hours = [];
        foreach (range(0, 6) as $day) {
            $hours[$day] = ['open_time' => '09:00', 'close_time' => '18:00', 'is_closed' => $day === 5 ? '1' : '0'];
        }

        $this->actingAs($this->owner)
            ->patch(route('settings.hours'), ['hours' => $hours])
            ->assertRedirect(route('settings.index', ['tab' => 'hours']));

        $friday = StoreHour::where('store_id', $this->store->id)->where('day_of_week', 5)->firstOrFail();
        $monday = StoreHour::where('store_id', $this->store->id)->where('day_of_week', 1)->firstOrFail();

        $this->assertTrue($friday->is_closed);
        $this->assertNull($friday->open_time);
        $this->assertSame('09:00', $monday->open_time);
    }

    public function test_a_blocked_date_can_be_added_and_removed(): void
    {
        $date = now()->addDays(30)->toDateString();

        $this->actingAs($this->owner)
            ->post(route('settings.blocked.store'), ['date' => $date, 'reason' => 'سفر'])
            ->assertRedirect();

        $blocked = BlockedDate::where('store_id', $this->store->id)->whereDate('date', $date)->firstOrFail();

        $this->actingAs($this->owner)
            ->delete(route('settings.blocked.destroy', $blocked))
            ->assertRedirect();

        $this->assertDatabaseMissing('blocked_dates', ['id' => $blocked->id]);
    }

    public function test_a_blocked_date_from_another_store_is_not_found(): void
    {
        $other = Store::create([
            'owner_id' => User::create(['name' => 'مالك', 'email' => 'y@example.com', 'password' => 'secret1234'])->id,
            'name' => 'متجر ثانٍ',
            'slug' => 'second-store',
            'phone' => '0790000010',
        ]);

        $blocked = BlockedDate::create(['store_id' => $other->id, 'date' => now()->addDays(10), 'reason' => 'x']);

        $this->actingAs($this->owner)
            ->delete(route('settings.blocked.destroy', $blocked))
            ->assertNotFound();
    }
}
