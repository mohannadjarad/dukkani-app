<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * A product photo that stops loading must never reach the customer as a broken
 * frame, and the owner must be told which product is affected by name.
 */
class ProductImageHealthTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
        $this->product = $this->store->products()->where('slug', 'honey-almond-cake')->firstOrFail();
    }

    public function test_a_working_image_is_shown_as_stored(): void
    {
        $this->assertFalse($this->product->imageIsBroken());

        $this->get(route('storefront.menu', $this->store))
            ->assertOk()
            ->assertSee('images/amal-bakes/honey-almond-cake.webp');
    }

    public function test_a_broken_image_is_replaced_by_the_placeholder_and_never_served(): void
    {
        $this->product->forceFill([
            'image_path' => 'https://res.cloudinary.com/demo/image/upload/v1/dead/cake.jpg',
            'image_broken_at' => now(),
        ])->save();

        $this->get(route('storefront.menu', $this->store))
            ->assertOk()
            ->assertDontSee('dead/cake.jpg')
            ->assertSee(Product::placeholderUrl());

        $this->get(route('storefront.product', [$this->store, $this->product]))
            ->assertOk()
            ->assertDontSee('dead/cake.jpg');
    }

    public function test_a_product_without_an_image_is_not_reported_as_broken(): void
    {
        $this->product->forceFill(['image_path' => null])->save();

        $this->assertFalse($this->product->imageIsBroken());

        $this->get(route('storefront.menu', $this->store))
            ->assertOk()
            ->assertSee(Product::placeholderUrl());
    }

    public function test_a_signed_report_marks_a_dead_image_as_broken(): void
    {
        $this->product->forceFill([
            'image_path' => 'https://cdn.example.com/missing.jpg',
        ])->save();

        Http::fake([
            'cdn.example.com/*' => Http::response('', 404),
        ]);

        $url = URL::temporarySignedRoute('storefront.image-problem', now()->addHour(), [
            'store' => $this->store,
            'product' => $this->product,
        ]);

        $this->post($url)->assertNoContent();

        $this->assertNotNull($this->product->fresh()->image_broken_at);
    }

    public function test_a_signed_report_keeps_a_healthy_image_visible(): void
    {
        // The seed image is a real file in public/images; one flaky mobile
        // request must not permanently hide it.
        $url = URL::temporarySignedRoute('storefront.image-problem', now()->addHour(), [
            'store' => $this->store,
            'product' => $this->product,
        ]);

        $this->post($url)->assertNoContent();

        $this->assertNull($this->product->fresh()->image_broken_at);
    }

    public function test_an_unsigned_report_is_rejected(): void
    {
        $this->post(route('storefront.image-problem', [$this->store, $this->product]))
            ->assertForbidden();

        $this->assertNull($this->product->fresh()->image_broken_at);
    }

    public function test_a_report_cannot_flag_another_stores_product(): void
    {
        $otherStore = Store::create([
            'owner_id' => $this->store->owner_id,
            'name' => 'متجر ثانٍ',
            'slug' => 'second-store',
            'phone' => '0790000001',
            'currency' => 'JOD',
            'country_code' => '962',
            'timezone' => 'Asia/Amman',
            'status' => 'active',
        ]);

        $url = URL::temporarySignedRoute('storefront.image-problem', now()->addHour(), [
            'store' => $otherStore,
            'product' => $this->product,
        ]);

        $this->post($url)->assertNotFound();

        $this->assertNull($this->product->fresh()->image_broken_at);
    }

    public function test_the_dashboard_names_the_product_whose_image_broke(): void
    {
        $this->product->forceFill([
            'image_path' => 'https://cdn.example.com/missing.jpg',
            'image_broken_at' => now(),
        ])->save();

        $owner = User::findOrFail($this->store->owner_id);

        $this->actingAs($owner)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('في مشكلة في صورة منتج واحد')
            ->assertSee('صورة «كيكة العسل واللوز» ما بتفتح');
    }

    public function test_the_edit_page_explains_the_image_problem(): void
    {
        $this->product->forceFill([
            'image_path' => 'https://cdn.example.com/missing.jpg',
            'image_broken_at' => now(),
        ])->save();

        $owner = User::findOrFail($this->store->owner_id);

        $this->actingAs($owner)
            ->get(route('products.edit', $this->product))
            ->assertOk()
            ->assertSee('في مشكلة في صورة «كيكة العسل واللوز»')
            ->assertSee('الرابط المخزَّن ما بيفتح');
    }

    public function test_saving_a_new_image_clears_the_warning(): void
    {
        $this->product->forceFill([
            'image_path' => 'https://cdn.example.com/missing.jpg',
            'image_broken_at' => now(),
        ])->save();

        $owner = User::findOrFail($this->store->owner_id);

        $this->actingAs($owner)
            ->patch(route('products.update', $this->product), [
                'name' => $this->product->name,
                'price' => 18.5,
                'image_url' => 'https://cdn.example.com/fresh.jpg',
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.index'));

        $this->assertNull($this->product->fresh()->image_broken_at);
    }

    public function test_saving_the_same_dead_link_keeps_the_warning(): void
    {
        $dead = 'https://cdn.example.com/missing.jpg';

        $this->product->forceFill(['image_path' => $dead, 'image_broken_at' => now()])->save();

        $owner = User::findOrFail($this->store->owner_id);

        $this->actingAs($owner)
            ->patch(route('products.update', $this->product), [
                'name' => $this->product->name,
                'price' => 18.5,
                'image_url' => $dead,
                'is_active' => '1',
            ]);

        $this->assertNotNull($this->product->fresh()->image_broken_at);
    }

    public function test_the_check_command_flags_dead_links_and_clears_recovered_ones(): void
    {
        $this->product->forceFill(['image_path' => 'https://cdn.example.com/cake.jpg'])->save();

        $status = 404;

        Http::fake([
            'cdn.example.com/*' => function () use (&$status) {
                return $status === 200
                    ? Http::response('', 200, ['Content-Type' => 'image/jpeg'])
                    : Http::response('', 404);
            },
        ]);

        $this->artisan('products:check-images')->assertSuccessful();

        $this->assertNotNull($this->product->fresh()->image_broken_at);

        $status = 200;

        $this->artisan('products:check-images')->assertSuccessful();

        $this->assertNull($this->product->fresh()->image_broken_at);
    }

    public function test_the_check_command_leaves_local_images_alone(): void
    {
        // Demo products point at files that ship with the app.
        Http::fake();

        $this->artisan('products:check-images')->assertSuccessful();

        $this->assertNull($this->product->fresh()->image_broken_at);
        Http::assertNothingSent();
    }
}