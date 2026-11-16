<?php

namespace Tests\Unit;

use App\Exceptions\ImageServiceNotConfiguredException;
use App\Models\Product;
use App\Models\Store;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    public function test_cloudinary_urls_get_delivery_transformations(): void
    {
        $url = 'https://res.cloudinary.com/demo/image/upload/v123/products/cake.jpg';

        $this->assertSame(
            'https://res.cloudinary.com/demo/image/upload/f_auto,q_auto,w_800/v123/products/cake.jpg',
            (new ImageService)->url($url, 'f_auto,q_auto,w_800'),
        );
    }

    public function test_an_existing_transformation_is_not_duplicated(): void
    {
        $url = 'https://res.cloudinary.com/demo/image/upload/f_auto,q_auto,w_400/v123/products/cake.jpg';

        $this->assertSame($url, (new ImageService)->url($url, 'f_auto,q_auto,w_400'));
    }

    public function test_legacy_and_external_urls_are_unchanged(): void
    {
        $service = new ImageService;

        $this->assertSame('images/amal-bakes/cake.webp', $service->url('images/amal-bakes/cake.webp', 'f_auto,q_auto,w_800'));
        $this->assertSame('https://example.com/cake.jpg', $service->url('https://example.com/cake.jpg', 'f_auto,q_auto,w_800'));
    }

    public function test_local_fallback_uses_the_public_disk(): void
    {
        Storage::fake('public');

        $result = (new ImageService)->upload(
            UploadedFile::fake()->create('cake.jpg', 10, 'image/jpeg'),
            'products',
        );

        $this->assertNull($result['public_id']);
        $this->assertStringContainsString('/storage/', $result['url']);
    }

    public function test_uploads_are_rejected_outside_local_and_testing(): void
    {
        $this->app['env'] = 'production';
        config(['services.cloudinary.url' => null, 'app.demo_mode' => false]);

        $this->expectException(ImageServiceNotConfiguredException::class);

        (new ImageService)->upload(
            UploadedFile::fake()->create('cake.jpg', 10, 'image/jpeg'),
            'products',
        );
    }

    public function test_demo_mode_keeps_the_local_fallback_on_production(): void
    {
        Storage::fake('public');

        $this->app['env'] = 'production';
        config(['services.cloudinary.url' => null, 'app.demo_mode' => true]);

        $result = (new ImageService)->upload(
            UploadedFile::fake()->create('cake.jpg', 10, 'image/jpeg'),
            'products',
        );

        $this->assertNull($result['public_id']);
        $this->assertStringContainsString('/storage/', $result['url']);
    }

    public function test_product_and_store_models_apply_cloudinary_transformations(): void
    {
        $cloudinary = 'https://res.cloudinary.com/demo/image/upload/v123/products/cake.jpg';

        $product = new Product(['image_path' => $cloudinary]);
        $this->assertStringContainsString('/upload/f_auto,q_auto,w_800/', $product->imageUrl());
        $this->assertStringContainsString('/upload/f_auto,q_auto,w_400/', $product->thumbnailUrl());

        $store = new Store(['logo_path' => $cloudinary, 'cover_path' => $cloudinary]);
        $this->assertStringContainsString('/upload/f_auto,q_auto,w_800/', $store->imageUrl());
        $this->assertStringContainsString('/upload/f_auto,q_auto,w_800/', $store->logoUrl());
        $this->assertStringContainsString('/upload/f_auto,q_auto,w_800/', $store->coverUrl());
    }
}
