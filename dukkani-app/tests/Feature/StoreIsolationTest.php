<?php

namespace Tests\Feature;

use App\Models\Store;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Store $otherStore;

    protected User $otherOwner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);

        $this->otherOwner = User::create([
            'name' => 'مالك ثانٍ',
            'email' => 'other-owner@example.com',
            'password' => 'secret1234',
        ]);

        $this->otherStore = Store::create([
            'owner_id' => $this->otherOwner->id,
            'name' => 'متجر ثانٍ',
            'slug' => 'second-store',
            'phone' => '0790000001',
            'currency' => 'JOD',
            'country_code' => '962',
            'timezone' => 'Asia/Amman',
            'status' => 'active',
        ]);
    }

    public function test_a_user_resolves_only_their_own_store(): void
    {
        $demoStoreId = Store::where('slug', 'amal-bakes')->value('id');

        $this->assertSame($this->otherStore->id, $this->otherOwner->store->id);
        $this->assertNotSame($demoStoreId, $this->otherOwner->store->id);
    }

    public function test_the_orders_board_only_shows_the_authenticated_store_orders(): void
    {
        $demoStore = Store::where('slug', 'amal-bakes')->firstOrFail();
        $demoOrder = $demoStore->orders()->firstOrFail();

        $this->actingAs($this->otherOwner)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertDontSee($demoOrder->number);
    }

    public function test_another_store_order_cannot_be_opened(): void
    {
        $demoStore = Store::where('slug', 'amal-bakes')->firstOrFail();
        $demoOrder = $demoStore->orders()->firstOrFail();

        $this->actingAs($this->otherOwner)
            ->get(route('orders.show', $demoOrder))
            ->assertNotFound();
    }

    public function test_another_store_product_cannot_be_edited_or_deleted(): void
    {
        $demoStore = Store::where('slug', 'amal-bakes')->firstOrFail();
        $demoProduct = $demoStore->products()->firstOrFail();

        $this->actingAs($this->otherOwner)
            ->get(route('products.edit', $demoProduct))
            ->assertNotFound();

        $this->actingAs($this->otherOwner)
            ->delete(route('products.destroy', $demoProduct))
            ->assertNotFound();

        $this->assertDatabaseHas('products', ['id' => $demoProduct->id, 'deleted_at' => null]);
    }

    public function test_the_dashboard_only_shows_the_authenticated_store_orders(): void
    {
        $this->actingAs($this->otherOwner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('لا توجد طلبات بعد');
    }
}
