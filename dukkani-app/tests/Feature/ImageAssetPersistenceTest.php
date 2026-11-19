<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\ImageService;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Saving a form again must never delete an image it still points at. The edit
 * forms prefill the current URL; the old code treated that as a new external
 * link and destroyed the Cloudinary asset once the CDN cache expired.
 */
class ImageAssetPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;

    protected User $owner;

    protected Product $product;

    protected string $cloudinary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DemoSeeder::class);
        $this->store = Store::where('slug', 'amal-bakes')->firstOrFail();
        $this->owner = $this->store->owner;
        $this->product = $this->store->products()->where('slug', 'honey-almond-cake')->firstOrFail();
        $this->cloudinary = 'https://res.cloudinary.com/demo/image/upload/v123/products/cake.jpg';
    }

    public function test_saving_a_product_with_its_prefilled_cloudinary_url_keeps_the_asset(): void
    {
        $this->product->forceFill([
            'image_path' => $this->cloudinary,
            'image_public_id' => 'products/cake',
        ])->save();

        $this->mock(ImageService::class, function ($mock) {
            $mock->shouldNotReceive('delete');
            $mock->shouldNotReceive('upload');
        });

        $this->actingAs($this->owner)
            ->patch(route('products.update', $this->product), [
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => '18.5',
                'image_url' => $this->cloudinary,
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.index'));

        $fresh = $this->product->fresh();

        $this->assertSame($this->cloudinary, $fresh->image_path);
        $this->assertSame('products/cake', $fresh->image_public_id);
    }

    public function test_saving_a_product_without_an_image_field_keeps_the_asset(): void
    {
        $this->product->forceFill([
            'image_path' => $this->cloudinary,
            'image_public_id' => 'products/cake',
        ])->save();

        $this->mock(ImageService::class, function ($mock) {
            $mock->shouldNotReceive('delete');
            $mock->shouldNotReceive('upload');
        });

        $this->actingAs($this->owner)
            ->patch(route('products.update', $this->product), [
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => '18.5',
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.index'));

        $this->assertSame('products/cake', $this->product->fresh()->image_public_id);
    }

    public function test_replacing_a_cloudinary_image_deletes_only_the_old_asset(): void
    {
        $this->product->forceFill([
            'image_path' => $this->cloudinary,
            'image_public_id' => 'products/cake',
        ])->save();

        $this->mock(ImageService::class, function ($mock) {
            $mock->shouldReceive('delete')->once()->with('products/cake');
        });

        $this->actingAs($this->owner)
            ->patch(route('products.update', $this->product), [
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => '18.5',
                'image_url' => 'https://cdn.example.com/new-cake.jpg',
                'is_active' => '1',
            ])
            ->assertRedirect(route('products.index'));

        $fresh = $this->product->fresh();

        $this->assertSame('https://cdn.example.com/new-cake.jpg', $fresh->image_path);
        $this->assertNull($fresh->image_public_id);
    }

    public function test_saving_the_store_profile_with_prefilled_image_urls_keeps_the_assets(): void
    {
        $logo = 'https://res.cloudinary.com/demo/image/upload/v123/stores/logos/logo.jpg';
        $cover = 'https://res.cloudinary.com/demo/image/upload/v123/stores/covers/cover.jpg';

        $this->store->update([
            'logo_path' => $logo,
            'logo_public_id' => 'stores/logos/logo',
            'cover_path' => $cover,
            'cover_public_id' => 'stores/covers/cover',
        ]);

        $this->mock(ImageService::class, function ($mock) {
            $mock->shouldNotReceive('delete');
            $mock->shouldNotReceive('upload');
        });

        $this->actingAs($this->owner)
            ->patch(route('settings.profile'), [
                'name' => $this->store->name,
                'slug' => $this->store->slug,
                'phone' => $this->store->phone,
                'city' => $this->store->city,
                'logo_url' => $logo,
                'cover_url' => $cover,
            ])
            ->assertRedirect(route('settings.index', ['tab' => 'profile']));

        $fresh = $this->store->fresh();

        $this->assertSame('stores/logos/logo', $fresh->logo_public_id);
        $this->assertSame('stores/covers/cover', $fresh->cover_public_id);
    }

    public function test_a_local_product_upload_is_stored_as_a_relative_path(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)
            ->post(route('products.store'), [
                'name' => 'كيكة محلية',
                'price' => '5.000',
                'is_active' => '1',
                'image_file' => UploadedFile::fake()->create('cake.jpg', 10, 'image/jpeg'),
            ])
            ->assertRedirect(route('products.index'));

        $product = Product::where('name', 'كيكة محلية')->firstOrFail();

        $this->assertStringStartsWith('products/', $product->image_path);
        $this->assertStringNotContainsString('http', $product->image_path);
        $this->assertTrue(Storage::disk('public')->exists($product->image_path));
    }
}
