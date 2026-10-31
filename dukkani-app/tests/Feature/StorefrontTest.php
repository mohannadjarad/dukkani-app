<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
    }

    public function test_root_redirects_to_the_storefront(): void
    {
        $this->get('/')->assertRedirect(route('storefront.home', $this->store));
    }

    public function test_storefront_home_renders(): void
    {
        $this->get(route('storefront.home', $this->store))
            ->assertOk()
            ->assertSee('أمل بيكنز')
            ->assertSee('كيكة العسل واللوز')
            ->assertSee('ساعات العمل')
            ->assertSee('المنيو');
    }

    public function test_menu_can_be_filtered_by_category_and_search(): void
    {
        $category = $this->store->categories()->where('name', 'كيك ومناسبات')->firstOrFail();

        $this->get(route('storefront.menu', ['store' => $this->store, 'category' => $category->id]))
            ->assertOk()
            ->assertSee('كيكة العسل واللوز')
            ->assertDontSee('كرواسون الزبدة');

        $this->get(route('storefront.menu', ['store' => $this->store, 'q' => 'تشيز']))
            ->assertOk()
            ->assertSee('تشيز كيك التوت')
            ->assertDontSee('كيكة العسل واللوز');
    }

    public function test_product_page_shows_options_and_related_products(): void
    {
        $this->get(route('storefront.product', [$this->store, 'honey-almond-cake']))
            ->assertOk()
            ->assertSee('كيكة العسل واللوز')
            ->assertSee('الحجم')
            ->assertSee('كبير (10–12 شخص)')
            ->assertSee('قد يعجبك');
    }

    public function test_product_from_another_store_is_not_found(): void
    {
        $owner = User::factory()->create();

        $otherStore = Store::create([
            'owner_id' => $owner->id,
            'name' => 'متجر آخر',
            'slug' => 'other-store',
            'phone' => '0790000000',
        ]);

        $product = Product::create([
            'store_id' => $otherStore->id,
            'name' => 'منتج آخر',
            'slug' => 'other-product',
            'price_fils' => 1000,
        ]);

        $this->get(route('storefront.product', [$this->store, $product]))->assertNotFound();
    }

    public function test_adding_to_cart_requires_required_options(): void
    {
        $product = Product::where('slug', 'honey-almond-cake')->firstOrFail();

        $this->post(route('storefront.cart.add', $this->store), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('options');

        $this->assertEmpty(session('cart.'.$this->store->id));
    }

    public function test_items_can_be_added_updated_and_removed_from_the_cart(): void
    {
        $product = Product::where('slug', 'honey-almond-cake')->firstOrFail();
        $option = $product->options()->where('is_required', true)->firstOrFail();
        $value = $option->values()->firstOrFail();

        $this->post(route('storefront.cart.add', $this->store), [
            'product_id' => $product->id,
            'quantity' => 2,
            'options' => [$option->id => $value->id],
        ])->assertRedirect(route('storefront.product', [$this->store, $product]));

        $cart = session('cart.'.$this->store->id);
        $this->assertCount(1, $cart);

        $key = array_key_first($cart);
        $this->assertSame(2, $cart[$key]['quantity']);

        $this->get(route('storefront.cart', $this->store))
            ->assertOk()
            ->assertSee('كيكة العسل واللوز')
            ->assertSee('الحجم');

        $this->patch(route('storefront.cart.update', [$this->store, $key]), [
            'quantity' => 4,
        ])->assertRedirect(route('storefront.cart', $this->store));

        $this->assertSame(4, session('cart.'.$this->store->id)[$key]['quantity']);

        $this->delete(route('storefront.cart.remove', [$this->store, $key]))
            ->assertRedirect(route('storefront.cart', $this->store));

        $this->assertEmpty(session('cart.'.$this->store->id));
    }
}