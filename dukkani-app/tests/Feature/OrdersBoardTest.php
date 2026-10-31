<?php

namespace Tests\Feature;

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\MessageLog;
use App\Models\Order;
use App\Models\Store;
use App\Models\StoreNotification;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersBoardTest extends TestCase
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

    public function test_guests_are_redirected_to_login_from_the_orders_board(): void
    {
        $this->get('/dashboard/orders')->assertRedirect('/login');
    }

    public function test_the_orders_board_lists_seeded_orders(): void
    {
        $order = Order::where('status', OrderStatus::New)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('الطلبات')
            ->assertSee($order->number)
            ->assertSee('عربونات معلّقة');
    }

    public function test_the_status_filter_only_returns_matching_orders(): void
    {
        $new = Order::where('status', OrderStatus::New)->firstOrFail();
        $delivered = Order::where('status', OrderStatus::Delivered)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('orders.index', ['status' => 'new']))
            ->assertOk()
            ->assertSee($new->number)
            ->assertDontSee($delivered->number);
    }

    public function test_orders_can_be_searched_by_customer_name(): void
    {
        $order = Order::with('customer')->where('status', OrderStatus::Confirmed)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('orders.index', ['q' => $order->customer->name]))
            ->assertOk()
            ->assertSee($order->number);
    }

    public function test_the_board_view_renders_workflow_columns(): void
    {
        $this->actingAs($this->owner)
            ->get(route('orders.index', ['view' => 'board']))
            ->assertOk()
            ->assertSee('لوحة')
            ->assertSee('قيد التحضير')
            ->assertSee('تم التسليم');
    }

    public function test_the_order_detail_page_renders_for_the_owner(): void
    {
        $order = Order::with('customer')->where('status', OrderStatus::Confirmed)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee($order->customer->name)
            ->assertSee('سير العمل')
            ->assertSee('سجل النشاط')
            ->assertSee('رسائل واتساب');
    }

    public function test_whatsapp_links_target_the_customer_not_the_store(): void
    {
        $order = Order::with('customer')->where('status', OrderStatus::Confirmed)->firstOrFail();

        // A local Jordanian number such as 0791111001 becomes 962791111001.
        $customerNumber = '962'.ltrim(preg_replace('/\D+/', '', $order->customer->phone), '0');
        $storeNumber = preg_replace('/\D+/', '', $this->store->whatsapp);

        $this->assertNotSame($customerNumber, $storeNumber);

        $this->actingAs($this->owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('https://wa.me/'.$customerNumber, false)
            ->assertDontSee('https://wa.me/'.$storeNumber, false);
    }

    public function test_a_cancelled_order_detail_renders_without_workflow_actions(): void
    {
        $order = Order::where('status', OrderStatus::Cancelled)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($order->number)
            ->assertSee('الطلب مغلق')
            ->assertDontSee('btn-ghost btn-sm text-danger', false);
    }

    public function test_a_delivered_order_detail_renders_the_message_history(): void
    {
        $order = Order::where('status', OrderStatus::Delivered)->whereHas('messages')->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('آخر الرسائل المسجّلة');
    }

    public function test_an_order_from_another_store_is_not_found(): void
    {
        $otherOrder = $this->foreignOrder();

        $this->actingAs($this->owner)
            ->get(route('orders.show', $otherOrder))
            ->assertNotFound();
    }

    public function test_status_can_be_advanced_and_records_an_event(): void
    {
        $order = Order::where('status', OrderStatus::New)->firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('orders.status', $order), ['status' => 'confirmed'])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertTrue($order->events()->where('type', 'status_changed')->exists());
    }

    public function test_an_invalid_status_transition_is_rejected(): void
    {
        $order = Order::where('status', OrderStatus::New)->firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('orders.status', $order), ['status' => 'delivered'])
            ->assertSessionHasErrors('status');

        $this->assertSame(OrderStatus::New, $order->refresh()->status);
    }

    public function test_a_deposit_can_be_marked_as_paid(): void
    {
        $order = Order::where('deposit_status', DepositStatus::Pending)->firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('orders.deposit', $order), ['deposit_status' => 'paid'])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame(DepositStatus::Paid, $order->deposit_status);
        $this->assertSame($order->total_fils - $order->deposit_fils, $order->balance_due_fils);
        $this->assertTrue($order->events()->where('type', 'deposit_paid')->exists());
    }

    public function test_internal_notes_are_saved(): void
    {
        $order = Order::firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('orders.notes', $order), ['internal_notes' => 'العميل يفضّل التوصيل بعد الظهر.'])
            ->assertRedirect();

        $this->assertSame('العميل يفضّل التوصيل بعد الظهر.', $order->refresh()->internal_notes);
        $this->assertTrue($order->events()->where('type', 'note_added')->exists());
    }

    public function test_opening_a_whatsapp_template_is_logged(): void
    {
        $order = Order::firstOrFail();
        $before = MessageLog::count();

        $this->actingAs($this->owner)
            ->post(route('orders.messages.store', $order), ['template' => 'order_confirmation'])
            ->assertRedirect();

        $this->assertSame($before + 1, MessageLog::count());
        $this->assertDatabaseHas('message_logs', ['order_id' => $order->id, 'template' => 'order_confirmation']);
        $this->assertTrue($order->events()->where('type', 'whatsapp_sent')->exists());
    }

    public function test_all_notifications_can_be_marked_as_read(): void
    {
        $this->assertTrue(StoreNotification::where('store_id', $this->store->id)->unread()->exists());

        $this->actingAs($this->owner)
            ->post(route('notifications.read'))
            ->assertRedirect();

        $this->assertFalse(StoreNotification::where('store_id', $this->store->id)->unread()->exists());
    }

    protected function foreignOrder(): Order
    {
        $owner = User::create(['name' => 'مالك آخر', 'email' => 'owner@example.com', 'password' => 'secret1234']);

        $store = Store::create([
            'owner_id' => $owner->id,
            'name' => 'متجر آخر',
            'slug' => 'other-store',
            'phone' => '0790000001',
        ]);

        $customer = Customer::create([
            'store_id' => $store->id,
            'name' => 'زبون آخر',
            'phone' => '0790000002',
        ]);

        return Order::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'number' => 'OT-260101-001',
            'status' => OrderStatus::New,
            'fulfillment_type' => 'pickup',
            'scheduled_at' => now()->addDay(),
            'subtotal_fils' => 5000,
            'total_fils' => 5000,
        ]);
    }
}
