<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
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

    // Products ---------------------------------------------------------------

    public function test_guests_are_redirected_from_products(): void
    {
        $this->get('/dashboard/products')->assertRedirect('/login');
    }

    public function test_the_products_page_lists_seeded_products(): void
    {
        $product = Product::where('store_id', $this->store->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('المنتجات')
            ->assertSee($product->name)
            ->assertSee('إدارة التصنيفات');
    }

    public function test_products_can_be_searched_and_filtered_by_status(): void
    {
        $product = Product::where('store_id', $this->store->id)->firstOrFail();
        $product->update(['is_active' => false]);

        $this->actingAs($this->owner)
            ->get(route('products.index', ['q' => $product->name, 'status' => 'hidden']))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($this->owner)
            ->get(route('products.index', ['status' => 'active']))
            ->assertOk()
            ->assertDontSee($product->name);
    }

    public function test_the_product_create_page_renders(): void
    {
        $this->actingAs($this->owner)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('منتج جديد')
            ->assertSee('الخيارات والإضافات');
    }

    public function test_a_product_can_be_created_with_options(): void
    {
        $category = $this->store->categories()->firstOrFail();

        $this->actingAs($this->owner)
            ->post(route('products.store'), [
                'name' => 'كيكة الليمون الطازج',
                'price' => '19.500',
                'category_id' => $category->id,
                'lead_time_hours' => 36,
                'is_active' => '1',
                'is_featured' => '1',
                'options' => [
                    [
                        'name' => 'الحجم',
                        'type' => 'single',
                        'is_required' => '1',
                        'values' => [
                            ['value' => 'وسط', 'price_delta' => '0'],
                            ['value' => 'كبير', 'price_delta' => '5.000'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('store_id', $this->store->id)->where('name', 'كيكة الليمون الطازج')->firstOrFail();

        $this->assertSame(19500, $product->price_fils);
        $this->assertSame(36, $product->lead_time_hours);
        $this->assertTrue($product->is_featured);
        $this->assertNotEmpty($product->slug);
        $this->assertSame(1, $product->options()->count());
        $this->assertSame(5000, $product->options()->first()->values()->where('value', 'كبير')->value('price_delta_fils'));
    }

    public function test_creating_a_product_requires_a_name_and_price(): void
    {
        $this->actingAs($this->owner)
            ->from(route('products.create'))
            ->post(route('products.store'), ['price' => '0'])
            ->assertSessionHasErrors(['name', 'price']);
    }

    public function test_a_product_can_be_updated_and_options_are_replaced(): void
    {
        $product = Product::where('store_id', $this->store->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('products.update', $product), [
                'name' => 'كيكة العسل واللوز (محدّثة)',
                'price' => '20.000',
                'is_active' => '1',
                'options' => [
                    ['name' => 'النوع', 'type' => 'single', 'values' => [['value' => 'كلاسيك', 'price_delta' => '1.500']]],
                ],
            ])
            ->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('كيكة العسل واللوز (محدّثة)', $product->name);
        $this->assertSame(20000, $product->price_fils);
        $this->assertSame(1, $product->options()->count());
        $this->assertSame('النوع', $product->options()->first()->name);
    }

    public function test_product_availability_can_be_toggled(): void
    {
        $product = Product::where('store_id', $this->store->id)->where('is_active', true)->firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('products.availability', $product))
            ->assertRedirect();

        $this->assertFalse($product->refresh()->is_active);
    }

    public function test_a_product_from_another_store_is_not_found(): void
    {
        $product = Product::where('store_id', $this->foreignStore()->id)->firstOrFail();

        $this->actingAs($this->owner)->get(route('products.edit', $product))->assertNotFound();
        $this->actingAs($this->owner)->patch(route('products.update', $product), ['name' => 'x', 'price' => '1'])->assertNotFound();
        $this->actingAs($this->owner)->delete(route('products.destroy', $product))->assertNotFound();
    }

    public function test_a_category_can_be_created_and_deleted(): void
    {
        $this->actingAs($this->owner)
            ->post(route('products.categories.store'), ['name' => 'حلويات باردة'])
            ->assertRedirect();

        $category = $this->store->categories()->where('name', 'حلويات باردة')->firstOrFail();

        $this->actingAs($this->owner)
            ->delete(route('products.categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_duplicate_category_names_are_rejected(): void
    {
        $category = $this->store->categories()->firstOrFail();

        $this->actingAs($this->owner)
            ->from(route('products.index'))
            ->post(route('products.categories.store'), ['name' => $category->name])
            ->assertSessionHasErrors('name');
    }

    public function test_hidden_products_do_not_appear_on_the_storefront(): void
    {
        $product = Product::where('store_id', $this->store->id)->where('is_active', true)->firstOrFail();
        $product->update(['is_active' => false]);

        $this->get(route('storefront.menu', $this->store))
            ->assertOk()
            ->assertDontSee($product->name);
    }

    // Customers --------------------------------------------------------------

    public function test_guests_are_redirected_from_customers(): void
    {
        $this->get('/dashboard/customers')->assertRedirect('/login');
    }

    public function test_the_customers_page_lists_seeded_customers(): void
    {
        $customer = $this->store->customers()->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('العملاء')
            ->assertSee($customer->name)
            ->assertSee('إجمالي العملاء');
    }

    public function test_customers_can_be_searched_and_filtered_by_tag(): void
    {
        $vip = $this->store->customers()->whereJsonContains('tags', 'vip')->firstOrFail();

        $this->actingAs($this->owner)
            ->get(route('customers.index', ['q' => $vip->name]))
            ->assertOk()
            ->assertSee($vip->name);

        $this->actingAs($this->owner)
            ->get(route('customers.index', ['tag' => 'vip']))
            ->assertOk()
            ->assertSee($vip->name);
    }

    public function test_the_customer_profile_shows_order_history(): void
    {
        $customer = $this->store->customers()->has('orders')->firstOrFail();
        $order = $customer->orders()->first();

        $this->actingAs($this->owner)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee($order->number)
            ->assertSee('سجل الطلبات')
            ->assertSee('بيانات العميل');
    }

    public function test_a_customer_can_be_updated_with_tags_and_notes(): void
    {
        $customer = $this->store->customers()->firstOrFail();

        $this->actingAs($this->owner)
            ->patch(route('customers.update', $customer), [
                'name' => 'نور الخالدي',
                'phone' => $customer->phone,
                'email' => 'noor@example.com',
                'address' => 'عمّان، عبدون',
                'notes' => 'تفضّل التوصيل بعد الظهر.',
                'tags' => 'vip، متكرر، شركات',
            ])
            ->assertRedirect();

        $customer->refresh();

        $this->assertSame('noor@example.com', $customer->email);
        $this->assertSame('تفضّل التوصيل بعد الظهر.', $customer->notes);
        $this->assertSame(['vip', 'متكرر', 'شركات'], $customer->tags);
    }

    public function test_a_customer_phone_must_be_unique_inside_the_store(): void
    {
        $customer = $this->store->customers()->firstOrFail();
        $other = $this->store->customers()->whereKeyNot($customer->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->from(route('customers.show', $customer))
            ->patch(route('customers.update', $customer), [
                'name' => $customer->name,
                'phone' => $other->phone,
            ])
            ->assertSessionHasErrors('phone');
    }

    public function test_a_customer_from_another_store_is_not_found(): void
    {
        $customer = $this->foreignStore()->customers()->firstOrFail();

        $this->actingAs($this->owner)->get(route('customers.show', $customer))->assertNotFound();
    }

    protected function foreignStore(): Store
    {
        $owner = User::create(['name' => 'مالك آخر', 'email' => 'other-owner@example.com', 'password' => 'secret1234']);

        $store = Store::create([
            'owner_id' => $owner->id,
            'name' => 'متجر آخر',
            'slug' => 'other-store',
            'phone' => '0790000001',
        ]);

        $category = Category::create(['store_id' => $store->id, 'name' => 'فئة', 'is_active' => true]);

        Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'منتج آخر',
            'slug' => 'other-product',
            'price_fils' => 5000,
            'is_active' => true,
        ]);

        Customer::create([
            'store_id' => $store->id,
            'name' => 'زبون آخر',
            'phone' => '0790000002',
        ]);

        return $store;
    }
}
